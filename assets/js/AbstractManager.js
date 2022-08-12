import Ajax from './utils/ajax'
import AlertMessage from './utils/AlertMessage'
import Counter from './utils/Counter'
import ComponentHydrator from './ComponentHydrator'
import Loader from './utils/loader'
import ModalConfirmation from './utils/ModalConfirmation'
import RedirectChecker from './utils/RedirectChecker'
import {Modal, Tooltip} from 'bootstrap'
/**
 * Manage an object : create, update, delete, restore...
 */
export default class AbstractManager {
    /**
     * @param {string} objectName // the name of object entity
     * @param {string |null} containerSelector // the selector of container's elements
     * @param {Object | null} modalOptions // options of modal
     */
    constructor(objectName, containerSelector = null, modalOptions, modalConfirmSelector) {
        this.objectName = objectName
        this.containerElt = document.querySelector(containerSelector ?? `#container_${objectName}s`)
        this.form = null

        if (this.containerElt === null) {
            throw new Error('No container element')
        }

        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.componentHydrator = new ComponentHydrator()

        const modalSelector = '#modal_' + objectName
        this.modalElt = document.querySelector(modalSelector)

        if (this.modalElt) {
            this.objectModal = new Modal(this.modalElt, modalOptions)
        }

        this.supportId = document.querySelector('div[data-support-id]')?.dataset.supportId ?? null
        this.objectId = null

        // Requests
        this.requestShow = (id) => this.request('show', id, 'GET')
        this.requestDelete = (id) => this.request('delete', id, 'DELETE')
        this.requestRestore = (id) => this.request('restore', id, 'GET')

        // Paths
        this.pathCreate = (id) => this.getPath('create', id)
        this.pathEdit = (id) => this.getPath('edit', id)
        this.pathShowSupport = (id) => this.getPath('show-support', id)

        this.counters = []

        this.modalConfirmation = new ModalConfirmation(modalConfirmSelector, null, () => this.requestDelete(this.objectId))

        this.#init()
    }

    #init() {
        document.querySelector(`[data-action="new_${this.objectName}"]`)?.addEventListener('click', (e) => this.new(e))

        this.containerElt.querySelectorAll(`[data-${this.objectName}-id]`).forEach(elt => {
            this.addListenersToElt(elt)
        })

        document.querySelectorAll(`[data-counter="${this.objectName}"]`).forEach(counter => {
            this.counters.push(new Counter(counter))
        })

        this.#checkParamaters()
    }

    /**
     * Display a empty form in the modal.
     * 
     * @param {Event} e
     */
     new(e) {
        this.form.new(e)
        this.objectModal.show()
    }

    /**
     * Send Ajax request.
     * 
     * @param {string} action
     * @param {string | null} id
     * @param {string} method
     */
     request(action, id, method = 'GET') {
        // console.log(action, id, method)
        if (this.loader.isActive() === false) {
            this.ajax.send(method, this.getPath(action, id), this.responseAjax.bind(this))
        }
    }

    /**
     * Get the path to request with action key-word (create, edit; delete, restore...).
     * 
     * @param {string} action 
     * @param {string | null} id 
     * @returns {string}
     */
     getPath(action, id = null) {
        this.objectId = id ?? this.objectId

        const path = this.containerElt.getAttribute('data-path-' + action)

        if (!path) {
            throw new Error('No data-path-'  + action + ' in the container.')
        }

        return path.replace('__id__', this.objectId)
    }

    responseAjax(response) {
        this.checkActions(response)

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        this.objectModal?.hide()
    }

    /**
     * Check the actions after the ajax response.
     * 
     * @param {Object} response 
     * @param {Object | null} object 
     * @param {boolean} closeModalAfter 
     */
     checkActions(response, object, closeModalAfter = true) {
        if (!response) {
            throw new Error('No response object.')
        }
        if (!response.action) {
            throw new Error('No action in the response.')
        }
        if (!object) {
            throw new Error('No object.')
        }

        switch (response.action) {
            case 'create':
                this.addElt(object, closeModalAfter)
                break
            case 'show':
                this.showForm(object)
                break
            case 'update':
                this.updateElt(object, closeModalAfter)
                break
            case 'delete':
                this.deleteElt(object)
                break
            case 'restore':
                this.restoreElt(object)
                break
        }

        if (response.alert === 'danger') {
            console.error(response.error)
        }

        this.extraActions?.(response, object)
    }

    /**
     * Add the default event listeners to a object element (show, delete, restore).
     * 
     * @param {HTMLElement} elt 
     */
     addListenersToElt(elt) {
        const id = elt.getAttribute(`data-${this.objectName}-id`)
        // Show object
        elt.querySelectorAll(`[data-action="show"]`).forEach(subElt => {
            subElt.addEventListener('click', e => {
                e.preventDefault()
                this.requestShow(id)
            })
        })
        // Delete object
        elt.querySelector('[data-action="delete"]')
            ?.addEventListener('click', () => this.showModalConfirm(id))
        // Restore object
        elt.querySelector('[data-action="restore"]')
            ?.addEventListener('click', () => this.requestRestore(id))

        // Check if addionnals event listeners in the child class
        this.extraListenersToElt?.(elt)
    }

    /**
     * Show the form in modal.
     * 
     * @param {Object} object
     */
     showForm(object) {
        this.form.show(object)
        this.objectModal.show()
    }

    /**
     * Show the modal confirmation before to do something. 
     *
     * @param {string} id
     */
     showModalConfirm(id) {
        this.objectId = !id ? this.objectId : id
        this.modalConfirmation.show()
     }

    /**
     * Create a object element in the DOM.
     *
     * @param {Object} object
     * @param {boolean} closeModalAfter
     */
     addElt(object, closeModalAfter = true) {
        const containertag = this.containerElt.dataset.containerTag
        let elt = document.createElement(containertag ?? 'div')

        elt.innerHTML = this.containerElt.dataset.prototype
        
        if(!containertag) {
            elt = elt.firstChild
        }

        elt.setAttribute(`data-${this.objectName}-id`, object.id)

        this.containerElt.insertBefore(elt, this.containerElt.firstChild)

        this.updateElt(object, closeModalAfter)

        this.addListenersToElt(elt)

        // Add tooltips
        elt.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(tooltip => new Tooltip(tooltip))
        
        this.counters.forEach(counter => counter.increment())

        if (closeModalAfter === true) {
            this.objectModal?.hide()
        }
     }

    /**
     * Update a object element in the DOM.
     *
     * @param {Object} object
     * @param {boolean} closeModalAfter
     * 
     * @returns {HTMLElement}
     */
     updateElt(object, closeModalAfter = true) {
        const objectElt = this.findElt(object.id)

        this.componentHydrator.hydrate(object, objectElt)

        // Check if addionnals updates in the child class
        this.extraUpdatesElt?.(object, objectElt)

        if (closeModalAfter === true) {
            this.objectModal?.hide()
        }
        
        return objectElt
     }

    /**
     * Delete object element in the DOM.
     *
     * @param {Object} object
     */
     deleteElt(object) {
        this.findElt(object.id).remove()

        this.counters.forEach(counter => counter.decrement())

        this.objectModal?.hide()
     }

    /**
     * Delete object element in the DOM (the same of delete action).
     *
     * @param {Object} object
     */
     restoreElt(object) {
        this.deleteElt(object)

        new RedirectChecker(
            this.containerElt.querySelectorAll(`[data-${this.objectName}-id]`).length === 0,
            null, 
            null, 
            'Vous allez être redirigé...'
        )
     }
     
    /**
     * @param {Object} data
     */
     getFile(data) {
        this.ajax.showFile(data.file, data.filename)
    }

    /**
     * Find the object element by data-attribut id in the DOM.
     *
     * @param {string} id 
     * 
     * @returns {HTMLElement}
     */
     findElt(id) {
        // console.log(id, this.objectName)
        const selector = `[data-${this.objectName}-id="${id}"]`
        const elt = this.containerElt.querySelector(selector)

        if (elt === null) {
            const message = `No element with ${selector} in the container.`

            new AlertMessage('danger', message)

            throw new Error(message)
        }

        return elt
    }


    /**
     * Find an element by data-object-key in the object element.
     *
     * @param {HTMLElement} objectElt 
     * @param {string} key 
     * 
     * @returns {HTMLElement}
     */
     findEltByDataObjectKey(objectElt, key) {
        const selector = `[data-object-key="${key}"]`
        const elt = objectElt.querySelector(selector)

        if (elt === null) {
            const message = `No element with ${selector} in the objectElt.`

            throw new Error(message)
        }

        return elt
    }

    /** 
     * Request to show object if the object id exists in the DOM.
     */
    #checkParamaters() {
        const urlParams = new URLSearchParams(window.location.search)
        const param = this.objectName + '_id'

        if (urlParams.has(param)) {
            this.requestShow(urlParams.get(param))
        }
    }
}