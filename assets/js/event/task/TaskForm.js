import AbstractForm from '../../utils/form/AbstractForm'
import TaskManager from "./TaskManager"
import AlertsManager from '../../utils/form/AlertsManager'

export default class TaskForm extends AbstractForm
{
    /**
     * @param {TaskManager} manager
     */
    constructor(manager) {
        super(manager)

        this.titleInputElt = this.formElt.querySelector('#task_title')
        this.endInputElt = this.formElt.querySelector('#task_end')
        this.endDateInputElt = this.formElt.querySelector('#task__endDate')
        this.endTimeInputElt = this.formElt.querySelector('#task__endTime')
        this.levelSelectElt = this.formElt.querySelector('#task_level')

        this.taskTitleElt = this.modalElt.querySelector('.modal-header h2')
        this.infoTaskElt = this.modalElt.querySelector('p[data-object-key="info"]')

        this.alertsManager = new AlertsManager(this.endInputElt)
        
        this.#init()
    }

    #init() {
        this.endDateInputElt.addEventListener('focusout', e => this.isValidDate(e.target, -(10 * 365), (2 * 365)))
    }

    new() {
        this.resetForm()

        this.taskTitleElt.textContent = this.getTitleModal(null, 'Nouvelle tâche')
        this.infoTaskElt.textContent = this.getCreateUpdateInfo()

        this.endDateInputElt.value = this.dateFormatter.getDateNow()
        this.endTimeInputElt.value = this.dateFormatter.getHour()
        this.#updateEndDate()

        this.alertsManager.reset()

        this.formData = new FormData(this.formElt)
    }

    /**
     * Show the task in th modal.
     * 
     * @param {Object} task
     */
     show(task) {
        this.hydrateForm(task)
       
        this.taskTitleElt.innerHTML = this.getTitleModal(task, 'Tâche')
        this.infoTaskElt.innerHTML = this.getCreateUpdateInfo(task)

        this.endDateInputElt.value = task.end.substr(0, 10)
        this.endTimeInputElt.value = task.end.substr(11, 5)
        this.#updateEndDate()

        this.alertsManager.init(task.alerts)

        this.formData = new FormData(this.formElt)
    }

    /**
     * Try to save the task.
     * 
     * @param {Event} e
     */
    requestToSave(e) {
        e.preventDefault()

        this.#updateEndDate()

        const formData = new FormData(this.formElt)
        formData.append(this.selectSupportElt.name, this.selectSupportElt.value)

        if (this.loader.isActive() === false && this.isValid()) {
            this.ajax.send('POST', this.formElt.action, this.responseAjax, formData)
        }

    }

    #updateEndDate() {
        if (isNaN(this.endDateInputElt.value) && isNaN(this.endTimeInputElt.value)) {
            this.endInputElt.value = this.endDateInputElt.value + 'T' + this.endTimeInputElt.value
        }
    }
}