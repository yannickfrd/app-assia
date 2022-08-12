import AbstractManager from '../../AbstractManager'
import TaskForm from './TaskForm'
import AlertMessage from '../../utils/AlertMessage'

export default class TaskManager extends AbstractManager {
    constructor() {
        super('task', null, {backdrop: 'static', keyboard: false})

        // Additionnal requests
        this.requestToggleStatus = (id) => this.request('toggle-status', id)

        if (!this.modalElt) {
            return this.initDashboard()
        }

        this.form = new TaskForm(this)
    } 

    /**
     * Initialize for dashboard page.
     */
    initDashboard() {
        this.containerElt.querySelectorAll('tr[data-task-id]').forEach(elt => {
            this.extraListenersToElt(elt)
            elt.querySelector('[data-action="show"]').addEventListener('click', e => {
                window.location.assign(e.currentTarget.dataset.path)
            })
        })
    }

    /**
     * Actions after Ajax response.
     * 
     * @param {Object} response 
     */
     responseAjax(response) {    
        const task = response.task

        this.checkActions(response, task)

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        this.objectModal?.hide()
    }

    /**     
     * Additionnal actions after the ajax response.
     */
    extraActions(response, task) {
        switch (response.action) {
            case 'toggle_status':
                this.#toggleStatus(task)
                break
        }
    }

    /**
     * Addionnal updates for the task <tr> element in the table.
     * 
     * @param {Object} task
     */
    extraUpdatesElt(task) {
        this.#toggleStatus(task)
    }

    /**
     * Addionnal event listeners to the object element.
     * 
     * @param {HTMLTableRowElement} trElt 
     */
    extraListenersToElt(trElt) {
        // Toggle task status
        trElt.querySelector('input[data-action="toggle_status"]')
            ?.addEventListener('click', () => this.requestToggleStatus(trElt.dataset.taskId))
    }

    /**
     * Toggle the task (done or not) in the table.
     * 
     * @param {Object} task
     */
     #toggleStatus(task) {
        const trElt = this.findElt(task.id)
        const inputElt = trElt.querySelector('input[data-action="toggle_status"]')
        const endTdElt = this.findEltByDataObjectKey(trElt, 'end')
        const spanElt = endTdElt.querySelector('span.fas.fa-exclamation')

        if (task.status === true) {
            trElt.classList.add('text-secondary', 'text-del')
            inputElt.checked = true
        } else {
            trElt.classList.remove('text-secondary', 'text-del')
            inputElt.checked = false
        }

        if (spanElt && task.status === false) {
            endTdElt.classList.add('text-danger')
            spanElt.classList.remove('d-none')
            return
        }

        endTdElt.classList.remove('text-danger')

        spanElt?.classList.add('d-none')
    }
}