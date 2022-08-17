import AbstractManager from '../../AbstractManager'
import SelectManager from './SelectManager'
import FormHydrator from './FormHydrator'
import FormValidator from './formValidator'
import DateFormatter from "../date/DateFormatter"

export default class AbstractForm extends FormValidator
{
    /**
     * @param {AbstractManager} manager 
     */
    constructor(manager) {
        super(`form[name="${manager.objectName}"]`)

        this.manager = manager
        this.objectName = manager.objectName
        this.loader = manager.loader
        this.ajax = manager.ajax
        this.responseAjax = (resp) => manager.responseAjax(resp)
        this.modalElt = manager.modalElt
        this.supportId = manager.supportId

        this.formElt = document.querySelector(`form[name="${this.objectName}"]`)
        this.btnDeleteElt = this.modalElt.querySelector('[data-action="delete"]')

        this.formData = null

        this.selectManagers = {}
        this.formElt.querySelectorAll('select[multiple], select[autocomplete="true"]').forEach(select => {
            this.selectManagers[select.id.split('_').pop()] = new SelectManager(select)
        })

        this.formHydrator = new FormHydrator(this.formElt, this.selectManagers)
        this.dateFormatter = new DateFormatter()

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
    }

    /**
     * @param {Object} object // entity
     */
     show(object) {
        this.initForm(object)

        this.formData = new FormData(this.formElt)
    }

    /**
     * @param {Event} e 
     */
    requestToSave(e) {
        e.preventDefault()

        this.formElt.classList.add('was-validated')

        if (this.loader.isActive() === false && this.isValid()) {
            this.ajax.send('POST', this.formElt.action, this.responseAjax, new FormData(this.formElt))
        }
    }

    /**
     * @param {Object} object // entity
     */
    initForm(object) {
        this.formElt.action = this.manager.pathEdit(object.id)

        this.reinitForm()

        this.formHydrator.hydrate(object)

        this.btnDeleteElt.classList.remove('d-none')

        this.focusFirstField()
    }

    resetForm() {
        this.formElt.action = this.manager.pathCreate(this.supportId)

        this.formElt.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(fieldElt => {
            fieldElt.value = fieldElt.dataset.defaultValue ?? ''
            fieldElt.disabled = fieldElt.value && fieldElt.dataset.disabledIfValue
        })
        
        this.formElt.querySelectorAll('input[type="checkbox"]').forEach(fieldElt => {
            fieldElt.checked = fieldElt.dataset.defaultValue === "true"
        })

        for (const [key, selectManager] of Object.entries(this.selectManagers)) {
            selectManager.clearItems()

            const defaultValue = selectManager.selectElt.dataset.defaultValue

            if (defaultValue) {
                selectManager.updateItems(defaultValue)
            }   
        }

        this.reinitForm()

        this.btnDeleteElt.classList.add('d-none')

        this.focusFirstField()
    }

    /**
     * Check if the form has modifications before to close modal.
     */
     tryCloseModal() {
        if (this.formDataIsChanged() === false
            || window.confirm(this.modalElt.dataset.confirmBeforeClose)
        ) {
            this.manager.objectModal.hide()
        }
    }

    /**
     * @param {Object | null} object
     * @param {string} title
     * 
     * @returns {string}
     */
     getTitleModal(object = null, title = 'Nouveau') {
        if (object === null || this.supportId || object.supportGroup == null) {
            return title
        }

        return `<a href="${this.manager.pathShowSupport(object.supportGroup.id)}" class="text-primary" 
            title="Accéder au suivi">${title} | ${object.supportGroup.header.fullname}</a>
        `
    }

    /**
     * Get the informations of creation and update.
     * 
     * @param {Object | null} object // entity
     * 
     * @returns {HTMLElement}
     */
     getCreateUpdateInfo(object = null) {
        if (object === null) {
            return ''
        }

        let content = 'Créé le ' + (object.createdAtToString ?? this.dateFormatter.format(object.createdAt))

        if (object.createdBy) {
            content += ' par ' + object.createdBy.fullname
        }

        if (object.createdAt !== object.updatedAt || object.createdAtToString !== object.updatedAtToString) {
            content += `<br/> (modifié le ${(object.updatedAtToString ?? this.dateFormatter.format(object.updatedAt))}
                ${object.updatedBy ? ' par ' + object.updatedBy.fullname : ''})`
        }

        return content
    }

    focusFirstField() {
        setTimeout(() => {
            this.formElt.querySelector('input, select').focus()
        }, 500)
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