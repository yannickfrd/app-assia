import AbstractManager from '../../AbstractManager'
import SelectManager from './SelectManager'

export default class AbstractForm {
    /**
     * @param {AbstractManager} manager 
     */
    constructor(manager) {
        this.manager = manager
        this.objectName = manager.objectName
        this.loader = manager.loader
        this.ajax = manager.ajax
        this.responseAjax = manager.responseAjax.bind(manager)
        this.modalElt = manager.modalElt

        this.formElt = document.querySelector(`form[name="${this.objectName}"]`)
        this.btnDeleteElt = this.modalElt.querySelector('[data-action="delete"]')

        this.selectTagsElt = document.querySelector(`#${this.objectName}_tags`)
        this.selectUsersElt = document.querySelector(`#${this.objectName}_users`)
        
        this.formData = null

        if (this.selectTagsElt) {
            this.tagsSelectManager = new SelectManager(this.selectTagsElt)
        }

        if (this.selectUsersElt) {
            this.usersSelectManager = new SelectManager(this.selectUsersElt)
        }

        this.#init()
    }

    #init() {
        this.formElt.querySelector('button[data-action="save"]').addEventListener('click', e => this.requestToSave(e))

        this.btnDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.manager.showModalConfirm()
        })

        if (this.modalElt.dataset.confirmBeforeClose) {
            this.modalElt.addEventListener('mousedown', e => {
                if (e.target === this.modalElt) this.tryCloseModal()
            })  
        }
    }

    new() {
        this.resetForm()

        this.formData = new FormData(this.formElt)

        this.focusFirstInput()
    }

    /**
     * @param {Object} object // entity
     */
     show(object) {
        this.hydrateForm(object)

        this.formData = new FormData(this.formElt)

        this.focusFirstInput()
    }

    /**
     * @param {Event} e 
     */
    requestToSave(e) {
        e.preventDefault()

        if (this.loader.isActive() === false) {
            this.ajax.send('POST', this.formElt.action, this.responseAjax, new FormData(this.formElt))
        }
    }

    /**
     * @param {Object} object // entity
     */
    hydrateForm(object) {
        this.formElt.action = this.manager.pathEdit(object.id)

        this.formElt.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(fieldElt => {
            const key = this.#getKey(fieldElt)
            const value = object[key]
            // console.log(key, value)
            if(value === undefined ||value instanceof Array) {
                return
            }

            if(value instanceof Object && value.id !== undefined) {
                return fieldElt.value = value.id 
            }

            fieldElt.value = value ?? ''
        })

        this.checkUsers(object)
        this.checkTags(object)

        this.btnDeleteElt.classList.remove('d-none')
    }

    resetForm() {
        this.formElt.action = this.manager.pathCreate(this.manager.supportId)

        this.formElt.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(fieldElt => {
            fieldElt.value = fieldElt.dataset.defaultValue ?? ''
        })

        if (this.selectTagsElt) {
            this.tagsSelectManager.clearItems()
        }

        if (this.selectUsersElt) {
            this.usersSelectManager.clearItems()
            this.usersSelectManager.updateItems(this.currentUserId)

            this.supportSelectElt.value = this.supportId ?? ''
            this.supportSelectElt.disabled = this.supportId !== null
        }
        
        this.btnDeleteElt.classList.add('d-none')
    }

    /**
     * Check if the form has modifications before to close modal.
     */
     tryCloseModal() {
        if (false === this.formDataIsChanged()
            || window.confirm(this.modalElt.dataset.confirmBeforeClose)
        ) {
            this.manager.objectModal.hide()
        }
    }

    /**
     * @param {Object} object // entity
     */
     checkUsers(object) {
        if (!this.selectUsersElt) {
            return
        }

        const userIds = []
        object.users.forEach(user => userIds.push(user.id))
        this.usersSelectManager.updateItems(userIds)
    }

    /**
     * @param {Object} object // entity
     */
    checkTags(object) {
        if (!this.selectTagsElt) {
            return
        }

        const tagsIds = []
        object.tags.forEach(tags => tagsIds.push(tags.id))
        this.tagsSelectManager.updateItems(tagsIds)
    }

    
    focusFirstInput() {
        setTimeout(() => {
            this.formElt.querySelector('input').focus()
        }, 500)
    }

    /**
     * @param {HTMLElement} fieldElt 
     * 
     * @returns {string}
     */
    #getKey(fieldElt) {
        return fieldElt.id.split('_').pop()
    }

    /**
     * Check if the FormData's values are modified.
     * 
     * @returns {boolean}
     */
    formDataIsChanged() {   
        let isChanged = false

        new FormData(this.formElt).forEach((newValue, newKey) => {
            let found = false
            this.formData.forEach((value, key) => { 
                if (key === newKey && value === newValue) {
                    return found = true
                }
            })
            if (found === false) {
                return isChanged = true
            }
        })

        return isChanged
    }
}