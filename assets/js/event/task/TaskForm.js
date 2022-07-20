import AlertMessage from '../../utils/AlertMessage'
import DateFormater from '../../utils/date/dateFormater'
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

        this.modalTaskElt = document.querySelector('#modal-task')

        this.formTaskElt = document.querySelector('form[name=task]')
        this.titleInputElt = document.getElementById('task_title')
        this.endInputElt = document.getElementById('task_end')
        this.endDateInputElt = document.getElementById('task__endDate')
        this.endTimeInputElt = document.getElementById('task__endTime')
        this.levelSelectElt = document.getElementById('task_level')
        this.statusCheckboxElt = document.getElementById('task_status')
        this.supportSelectElt = document.getElementById('task_supportGroup')
        this.usersSelecElt = document.getElementById('task_users')
        this.contentTextAreaElt = document.getElementById('task_content')

        this.taskTitleElt = this.modalTaskElt.querySelector('.modal-header h2')
        this.infoTaskElt = document.getElementById('js_task_info')
        this.btnSaveElt = document.getElementById('js-btn-save')
        this.btnDeleteElt = document.getElementById('modal-btn-delete')
        this.btnAddAlertElt = document.querySelector('button[data-add-widget]')

        this.currentUserId = document.getElementById('user-name').dataset.userId

        this.usersSelectManager = new SelectManager('#task_users')
        this.tagsSelectManager = new SelectManager('#task_tags')

        this.alertsCollectionManager = new WidgetCollectionManager(this.afterToAddAlert.bind(this), null, 3)

        this.formValidator = new FormValidator(this.formTaskElt)

        // this.supportPeopleSelectElt = document.getElementById('task_supportPeople')
        // this.supportPeopleSelect = new SelectManager('#task_supportPeople')
        this.init()
    }

    init() {
        this.endDateInputElt.addEventListener('focusout', e => this.isValidDate(e.target))

        this.btnSaveElt.addEventListener('click', e => this.requestSaveTask(e))
        // this.supportSelectElt.addEventListener('change', () => this.requestGetSupportPeople())
    }

    /**
     * Initialise les champs du formulaire.
     */
    resetForm() {
        this.formValidator.reinit()
        this.formTaskElt.action = this.formTaskElt.dataset.urlTaskNew

        this.taskTitleElt.textContent = 'Nouvelle tâche'
        this.infoTaskElt.textContent = ''

        const dateFormater = new DateFormater()
        this.endDateInputElt.value = dateFormater.getDateNow()
        this.endTimeInputElt.value = dateFormater.getHour()
        this.updateEndDate()

        this.titleInputElt.value = ''
        this.statusCheckboxElt.value = false
        this.statusCheckboxElt.checked = false
        this.supportSelectElt.value = this.supportId ?? ''
        this.supportSelectElt.disabled = this.supportId !== null
        this.contentTextAreaElt.value = ''
        this.levelSelectElt.value = this.levelSelectElt.dataset.defaultLevel

        this.usersSelectManager.updateItems(this.currentUserId)

        // this.supportPeopleId = []
        // this.supportPeopleSelectElt.value = ''
        // this.supportPeopleSelectElt.parentNode.classList.add('d-none')

        this.tagsSelectManager.clearItems()

        this.resetAlerts()

        this.btnDeleteElt.classList.add('d-none')
        this.btnSaveElt.classList.remove('d-none')
    }

    /**
     * Réinitialise les alertes du formulaire.
     */
    resetAlerts() {
        const alertprototype = document.querySelector('#alerts-fields-list')
        alertprototype.innerHTML = ''
        alertprototype.dataset.widgetCounter = 0
        this.btnAddAlertElt.classList.remove('d-none')
    }

    /**
     * Requête pour enregistrer la tâche.
     * @param {Event} e
     */
    requestSaveTask(e) {
        e.preventDefault()
        this.updateEndDate()

        if (this.loader.isActive()) {
            return
        }

        if (!this.isValidForm()) {
            return new AlertMessage('danger', 'Une ou plusieurs informations sont invalides.')
        }

        this.loader.on()

        const formData = new FormData(this.formTaskElt)
        formData.append(this.supportSelectElt.name, this.supportSelectElt.value)
        this.ajax.send('POST', this.formTaskElt.action, this.taskManager.responseAjax.bind(this.taskManager), formData)
    }

    /**
     * Affiche la tâche dans le formulaire modal.
     * @param {Object} task
     */
    showTask(task) {
        this.modalTaskElt.querySelector('form').action = '/task/' + task.id + '/edit'
        this.titleInputElt.value = task.title
        this.endDateInputElt.value = task.end.substr(0, 10)
        this.endTimeInputElt.value = task.end.substr(11, 5)
        this.endInputElt.value = task.end.substr(0, 16)

        this.levelSelectElt.value = task.level
        this.statusCheckboxElt.value = task.status
        this.statusCheckboxElt.checked = task.status
        this.contentTextAreaElt.value = task.content ?? ''

        this.taskTitleElt.innerHTML = this.getTitleModal(task)
        this.infoTaskElt.innerHTML = this.getInfoTaskElt(task)

        this.btnDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.taskManager.confirmDeleteModal.show()
            this.taskManager.btnConfirmDeleteElt.dataset.url = this.btnDeleteElt.dataset.url.replace('__id__', task.id)
        })

        const userIds = []
        task.users.forEach(user => userIds.push(user.id))
        this.usersSelectManager.updateItems(userIds)

        const tagsIds = []
        task.tags.forEach(tags => tagsIds.push(tags.id))
        this.tagsSelectManager.updateItems(tagsIds)

        this.supportSelectElt.value = ''
        this.supportSelectElt.disabled = task.supportGroup !== null
        if (task.supportGroup) {
            this.supportSelectElt.value = task.supportGroup.id
            if (this.supportSelectElt.value === '') {
                const optionElt = document.createElement('option')
                optionElt.value = task.supportGroup.id
                optionElt.textContent = task.supportGroup.header.fullname
                this.supportSelectElt.appendChild(optionElt)
                this.supportSelectElt.value = task.supportGroup.id
            }
            // this.changeSupportPeopleSelect(task.supportGroup.supportPeople)
        }
        // if (task.supportPeople) {
        //     const supportPeopleIds = new Array()
        //     task.supportPeople.forEach(supportPerson => supportPeopleIds.push(supportPerson.id + ''))
        //     this.supportPeopleSelect.updateItems(supportPeopleIds)
        // }

        this.initAlerts(task)

        this.btnDeleteElt.classList.remove('d-none')
    }

    /**
     * @param {Object} task
     * @returns {string}
     */
    getTitleModal(task) {
        if (this.supportId || task.supportGroup == null) {
            return 'Tâche'
        }

        return `<a href="${this.taskTitleElt.dataset.url.replace('__id__', task.supportGroup.id)}" 
            class="text-primary" title="Accéder au suivi" data-bs-toggle="tooltip" 
            data-bs-placement="bottom">Tâche | ${task.supportGroup.header.fullname}</a>
        `
    }

    /**
     * Donnes les informations sur l'enregistrement (créé le, créé par).
     * @param {Object} task
     * @returns {HTMLElement}
     */
    getInfoTaskElt(task) {
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

    updateEndDate() {
        if (isNaN(this.endDateInputElt.value) && isNaN(this.endTimeInputElt.value)) {
            this.endInputElt.value = this.endDateInputElt.value + 'T' + this.endTimeInputElt.value
        }
    }

    /**
     * Initialise les rappels du formulaire.
     * @param {Object} task
     */
    initAlerts(task) {
        this.resetAlerts()

        task.alerts.forEach(alert => {
            const alertElt = this.alertsCollectionManager.addElt(this.btnAddAlertElt)
            alertElt.querySelector('input').value = alert.date.slice(0, 19)
            alertElt.querySelector('select').value = alert.type
        })
    }

    /**
     * Définit une date et heure par défaut après l'ajout d'une alerte.
     */
    afterToAddAlert() {
        this.updateEndDate()
        const elt = this.alertsCollectionManager.listElt.lastElementChild
        const defaultDate = new Date(this.endInputElt.value)
        defaultDate.setDate(defaultDate.getDate() - 1)

        const inputDateElt = elt.querySelector('input')
        inputDateElt.value = new DateFormater().getDate(defaultDate, 'datetimeInput')
        inputDateElt.addEventListener('focusout', e => this.isValidDate(e.target))
    }

    /**
     * Vérifie si les champs du formulaire sont valides.
     * @returns {Boolean}
     */
    isValidForm() {
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

            if (fieldElt.type.includes('date') && this.isValidDate(fieldElt) == false) {
                isValid = false
            }
        })

        return isValid
    }

    /**
     *
     * @param {HTMLInputElement} inputDateElt
     * @returns {Boolean}
     */
    isValidDate(inputDateElt) {
        if (this.formValidator.checkDate(inputDateElt, -(10 * 365), (2 * 365), 'Date incorrecte', false) == false) {
            return false
        }
        return true
    }


    // requestGetSupportPeople() {
    //     if (this.supportSelectElt.value) {
    //         const url = this.supportPeopleSelectElt.dataset.url.replace('__id__', this.supportSelectElt.value)
    //         this.ajax.send('GET', url, this.taskManager.responseAjax.bind(this.taskManager))
    //     } else {
    //         this.changeSupportPeopleSelect()
    //     }
    // }

    // /**
    //  * @param {Array} supportPeople 
    //  */
    // changeSupportPeopleSelect(supportPeople = []) {
    //     this.supportPeopleSelectElt.parentNode.classList.remove('d-none')
    //     this.supportPeopleSelectElt.textContent = ''
    //     supportPeople.forEach(supportPerson => {
    //         const optionElt = document.createElement('option')
    //         optionElt.value = supportPerson.id
    //         optionElt.textContent = supportPerson.person.fullname
    //         this.supportPeopleSelectElt.add(optionElt)
    //     })

    //     this.supportPeopleSelect.checkSelect2Style()
    // }
}