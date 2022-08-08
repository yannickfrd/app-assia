import TaskManager from "./TaskManager"
import AlertMessage from '../../utils/AlertMessage'
import DateFormatter from '../../utils/date/DateFormatter'
import SelectManager from '../../utils/form/SelectManager'
import WidgetCollectionManager from '../../utils/form/WidgetCollectionManager'
import FormValidator from '../../utils/form/formValidator'

export default class TaskForm
{
    /**
     * @param {TaskManager} taskManager
     */
    constructor(taskManager) {
        this.taskManager = taskManager
        this.loader = taskManager.loader
        this.ajax = taskManager.ajax
        this.supportId = taskManager.supportId
        
        // Modal element
        this.taskModalElt = document.getElementById('modal_task')
        this.taskModal = this.taskManager.objectModal

        // Form fields
        this.formTaskElt = this.taskModalElt.querySelector('form[name=task]')
        this.titleInputElt = this.formTaskElt.querySelector('#task_title')
        this.endInputElt = this.formTaskElt.querySelector('#task_end')
        this.endDateInputElt = this.formTaskElt.querySelector('#task__endDate')
        this.endTimeInputElt = this.formTaskElt.querySelector('#task__endTime')
        this.levelSelectElt = this.formTaskElt.querySelector('#task_level')
        this.statusCheckboxElt = this.formTaskElt.querySelector('#task_status')
        this.supportSelectElt = this.formTaskElt.querySelector('#task_supportGroup')
        this.usersSelecElt = this.formTaskElt.querySelector('#task_users')
        this.tagsSelecElt = this.formTaskElt.querySelector('#task_tags')
        this.contentTextAreaElt = this.formTaskElt.querySelector('#task_content')

        // Others elements
        this.taskTitleElt = this.taskModalElt.querySelector('.modal-header h2')
        this.infoTaskElt = this.taskModalElt.querySelector('p[data-object-key="info"]')
        this.btnSaveElt = this.taskModalElt.querySelector('button[data-action="save"]')
        this.btnDeleteElt = this.taskModalElt.querySelector('button[data-action="delete"]')
        this.btnAddAlertElt = this.taskModalElt.querySelector('button[data-add-widget]')

        this.currentUserId = document.getElementById('user-name').dataset.userId

        this.usersSelectManager = new SelectManager(this.usersSelecElt)
        this.tagsSelectManager = new SelectManager(this.tagsSelecElt)
        this.alertsCollectionManager = new WidgetCollectionManager(this.#afterToAddAlert.bind(this), null, 3)
        this.formValidator = new FormValidator(this.formTaskElt)
       
        this.#init()
    }

    #init() {
        this.endDateInputElt.addEventListener('focusout', e => this.#isValidDate(e.target))

        this.btnSaveElt.addEventListener('click', e => this.#requestSave(e))

        this.btnDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.taskManager.showModalConfirm()
        })
    }

    new() {
        this.#resetForm()

        this.taskTitleElt.textContent = 'Nouvelle tâche'  

        this.formTaskElt.action = this.taskManager.pathCreate()
    }

    /**
     * Show the task in th modal.
     * 
     * @param {Object} task
     */
     show(task) {
        this.taskModalElt.querySelector('form').action = this.taskManager.pathEdit(task.id)
        this.titleInputElt.value = task.title
        this.endDateInputElt.value = task.end.substr(0, 10)
        this.endTimeInputElt.value = task.end.substr(11, 5)
        this.endInputElt.value = task.end.substr(0, 16)

        this.levelSelectElt.value = task.level
        this.statusCheckboxElt.value = task.status
        this.statusCheckboxElt.checked = task.status
        this.contentTextAreaElt.value = task.content ?? ''

        this.taskTitleElt.innerHTML = this.#getTitleModal(task)
        this.infoTaskElt.innerHTML = this.#getInfoTaskElt(task)

        const userIds = []
        task.users.forEach(user => userIds.push(user.id))
        this.usersSelectManager.updateItems(userIds)

        const tagsIds = []
        task.tags.forEach(tags => tagsIds.push(tags.id))
        this.tagsSelectManager.updateItems(tagsIds)
        
        this.supportSelectElt.disabled = task.supportGroup !== null

        if (task.supportGroup) {
            if (this.supportSelectElt.value === '') {
                const optionElt = document.createElement('option')
                optionElt.value = task.supportGroup.id
                optionElt.textContent = task.supportGroup.header.fullname
                this.supportSelectElt.appendChild(optionElt)
                this.supportSelectElt.value = task.supportGroup.id
            }
            this.supportSelectElt.value = task.supportGroup.id
        }

        this.#initAlerts(task)

        this.btnDeleteElt.classList.remove('d-none')
    }

    /**
     * Reinitialize the fields of form.
     */
    #resetForm() {
        this.formValidator.reinit()

        this.formTaskElt.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(fieldElt => {
            fieldElt.value = ''
        })

        this.infoTaskElt.textContent = ''

        const dateFormatter = new DateFormatter()
        this.endDateInputElt.value = dateFormatter.getDateNow()
        this.endTimeInputElt.value = dateFormatter.getHour()
        this.#updateEndDate()

        this.statusCheckboxElt.value = false
        this.statusCheckboxElt.checked = false

        this.levelSelectElt.value = this.levelSelectElt.dataset.defaultLevel

        this.usersSelectManager.updateItems(this.currentUserId)

        this.supportSelectElt.value = this.supportId ?? ''
        this.supportSelectElt.disabled = this.supportId !== null

        this.tagsSelectManager.clearItems()

        this.#resetAlerts()

        this.btnDeleteElt.classList.add('d-none')
        this.btnSaveElt.classList.remove('d-none')
    }

    /**
     * Reinitialize the alert elements of form.
     */
    #resetAlerts() {
        const alertprototype = document.querySelector('#alerts-fields-list')
        alertprototype.innerHTML = ''
        alertprototype.dataset.widgetCounter = 0
        this.btnAddAlertElt.classList.remove('d-none')
    }

    /**
     * Try to save the task.
     * 
     * @param {Event} e
     */
    #requestSave(e) {
        e.preventDefault()
        this.#updateEndDate()

        if (this.loader.isActive()) {
            return
        }

        if (!this.#isValidForm()) {
            return new AlertMessage('danger', 'Une ou plusieurs informations sont invalides.')
        }

        this.loader.on()

        const formData = new FormData(this.formTaskElt)
        formData.append(this.supportSelectElt.name, this.supportSelectElt.value)
        this.ajax.send('POST', this.formTaskElt.action, this.taskManager.responseAjax.bind(this.taskManager), formData)
    }

    /**
     * @param {Object} task
     * @returns {string}
     */
    #getTitleModal(task) {
        if (this.supportId || task.supportGroup == null) {
            return 'Tâche'
        }

        return `<a href="${this.taskManager.pathShowSupport(task.supportGroup.id)}" 
            class="text-primary" title="Accéder au suivi" data-bs-toggle="tooltip" 
            data-bs-placement="bottom">Tâche | ${task.supportGroup.header.fullname}</a>
        `
    }

    /**
     * Get the event informations (created at, created by...).
     * 
     * @param {Object} task
     * @returns {HTMLElement}
     */
    #getInfoTaskElt(task) {
        let htmlContent = `Créé le ${task.createdAtToString} `

        if (task.createdBy) {
            htmlContent = `${htmlContent} par ${task.createdBy.fullname} `
        }

        if (task.createdAtToString !== task.updatedAtToString) {
            htmlContent = `${htmlContent} <br/> (modifié le ${task.updatedAtToString}
                ${task.updatedBy ? ' par ' + task.updatedBy.fullname : ''})`
        }

        return htmlContent
    }

    #updateEndDate() {
        if (isNaN(this.endDateInputElt.value) && isNaN(this.endTimeInputElt.value)) {
            this.endInputElt.value = this.endDateInputElt.value + 'T' + this.endTimeInputElt.value
        }
    }

    /**
     * Initialize the alert elements of form.
     * 
     * @param {Object} task
     */
    #initAlerts(task) {
        this.#resetAlerts()

        task.alerts.forEach(alert => {
            const alertElt = this.alertsCollectionManager.addElt(this.btnAddAlertElt)
            alertElt.querySelector('input').value = alert.date.slice(0, 19)
            alertElt.querySelector('select').value = alert.type
        })
    }

    /**
     * Define a default datetime after to add a alert.
     */
    #afterToAddAlert() {
        this.#updateEndDate()
        const elt = this.alertsCollectionManager.listElt.lastElementChild
        const defaultDate = new Date(this.endInputElt.value)
        defaultDate.setDate(defaultDate.getDate() - 1)

        const inputDateElt = elt.querySelector('input')
        inputDateElt.value = new DateFormatter().format(defaultDate, 'datetimeInput')
        inputDateElt.addEventListener('focusout', e => this.#isValidDate(e.target))
    }

    /**
     * Check if the form fields are valids.
     * 
     * @returns {boolean}
     */
    #isValidForm() {
        let isValid = true
        const fieldElts = [
            this.titleInputElt,
            this.endDateInputElt,
            this.endTimeInputElt,
            this.levelSelectElt,
            this.usersSelecElt,
        ]

        this.formTaskElt.classList.add('was-validated')

        document.querySelector('#alerts-fields-list').querySelectorAll('input, select').forEach(fieldElt => fieldElts.push(fieldElt))

        fieldElts.forEach(fieldElt => {
            if (fieldElt.value === '') {
                isValid = false

                fieldElt.addEventListener('input', () => {
                    if (fieldElt.value === '') {
                        this.formValidator.invalidField(fieldElt, 'Saisie obligatoire.')
                    }
                    this.formValidator.validField(fieldElt)
                })
                return this.formValidator.invalidField(fieldElt, 'Saisie obligatoire.')
            }

            this.formValidator.validField(fieldElt, false)

            if (fieldElt.type.includes('date') && this.#isValidDate(fieldElt) == false) {
                isValid = false
            }
        })

        return isValid
    }

    /**
     * @param {HTMLInputElement} inputDateElt
     * @returns {boolean}
     */
    #isValidDate(inputDateElt) {
        if (this.formValidator.checkDate(inputDateElt, -(10 * 365), (2 * 365), 'Date incorrecte', false) == false) {
            return false
        }
        return true
    }
}