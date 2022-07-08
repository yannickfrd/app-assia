import Loader from '../../utils/loader'
import Ajax from '../../utils/ajax.js'
import TaskForm from './TaskForm.js'
import MessageFlash from '../../utils/messageFlash'
import {Modal} from 'bootstrap'

export default class TaskManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.btnNewTask = document.getElementById('js_new_task')
        this.modalTaskElt = document.getElementById('modal-task')

        if (!this.modalTaskElt) {
            return this.initToggleTaskStatus()
        }

        this.taskModal = new Modal(this.modalTaskElt)
        this.confirmDeleteModal = new Modal(document.getElementById('modal-block'))

        const divSupportElt = document.querySelector('div[data-support]')
        this.supportId = divSupportElt ? divSupportElt.dataset.support : null
        this.themeColor = document.getElementById('header').dataset.color
        this.counterTasksElt = document.getElementById('count_tasks')
        this.btnConfirmDeleteElt = document.querySelector('button#modal-confirm')

        this.taskForm = new TaskForm(this)

        this.init()
    }

    init() {
        document.querySelectorAll('button[data-action="restore"]').forEach(restoreBtn => restoreBtn
            .addEventListener('click', () => this.requestRestoreTask(restoreBtn)))

        if (this.btnNewTask) {
            this.btnNewTask.addEventListener('click', () => this.taskForm.resetForm())
        }

        document.querySelectorAll('button[data-action="edit_task"]').forEach(btnElt => {
            btnElt.addEventListener('click', () => this.requestShowTask(btnElt));
        })

        document.querySelectorAll('button[data-action="delete_task"]').forEach(btnElt => {
            btnElt.addEventListener('click', () => this.onClickDeleteTask(btnElt))
        })

        this.btnConfirmDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('DELETE', this.btnConfirmDeleteElt.dataset.url, this.responseAjax.bind(this))
        })

        this.initToggleTaskStatus()

        const urlParams = new URLSearchParams(window.location.search)

        if (urlParams.has('task_id')) {
            this.ajax.send('GET', this.getUrlTaskShow(urlParams.get('task_id')), this.responseAjax.bind(this))
        }
    }

    initToggleTaskStatus() {
        document.querySelectorAll('input[data-action="toggle_task_status"]').forEach(checkboxElt => {
            checkboxElt.addEventListener('click', () => this.requestToggleStatusTask(checkboxElt))
        })
    }

    requestRestoreTask(restoreBtn) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', restoreBtn.dataset.url, this.responseAjax.bind(this))
        }
    }

    /**
     * Requête pour voir la tâche sélectionnée dans le formulaire.
     * @param {HTMLButtonElement} btnEditElt
     */
    requestShowTask(btnEditElt) {
        this.loader.on()
        this.ajax.send('GET', btnEditElt.dataset.url, this.responseAjax.bind(this))
    }

    /**
     * @param {HTMLInputElement} checkboxElt
     */
    requestToggleStatusTask(checkboxElt) {
        if (!this.loader.isActive()) {
            this.ajax.send('GET', checkboxElt.dataset.url, this.responseAjax.bind(this))
        }
    }

    /**
     * Réponse du serveur.
     * @param {Object} response
     */
    responseAjax(response) {
        if (response.msg) {
            this.messageFlash = new MessageFlash(response.alert, response.msg)
        }

        if (response.action) {
            const task = response.task

            switch (response.action) {
                case 'create':
                    this.createTaskTr(task)
                    break
                case 'show':
                    this.showTask(task)
                    break
                case 'edit':
                    this.editTaskTr(task)
                    break
                case 'delete':
                    this.deleteTaskTr(task, response.action)
                    break
                case 'restore':
                    this.deleteTaskTr(task, response.action)
                    this.checkToRedirect(this.messageFlash.delay)
                    break
                case 'toggle_status':
                    this.checkStatus(task)
                    break
                // case 'get_support_people':
                // this.changeSupportPeopleSelect(response.supportPeople)
                // break
            }
        }
    }

    /**
     * Ajoute la tâche dans le corps <tbody> du tableau.
     * @param {Object} task
     */
    createTaskTr(task) {
        const tbodyElt = document.querySelector('#table_tasks>tbody')
        const rowElt = document.createElement('tr')

        let htmlContent = `
            <td class="align-middle text-center">
                <div class="custom-control custom-checkbox custom-checkbox-${this.themeColor} text-dark ps-0" 
                    title="Cliquer pour changer le statut" data-bs-toggle="tooltip" data-bs-placement="bottom">
                    <div class="form-check">
                        <input type="checkbox" class="custom-control-input checkbox form-check-input"
                            id="toggle_task_status_${task.id}" ${task.status ? ' checked' : ''} 
                            data-action="toggle_task_status" data-url="/task/${task.id}/toggle-status">
                        <label class="custom-control-label form-check-label ms-2 cursor-pointer"
                            for="toggle_task_status_${task.id}"></label>
                    </div>
                </div>
            </td>
            <td class="align-middle text-center">
                <button data-url="${this.getUrlTaskShow(task.id)}" class="btn btn-${this.themeColor} btn-sm"
                    data-action="edit_task" title="Voir/Modifier la tâche" data-bs-toggle="modal" data-bs-placement="bottom">
                    <span class="fas fa-eye"></span>
                </button>
            </td>
            <td class="align-middle" data-cell="title">${task.title}</td>
            <td class="align-middle" data-cell="end">${task.endToString}</td>
            <td class="align-middle" data-cell="alerts">${this.createAlerts(task)}</td>
            <td class="align-middle" data-cell="level">${task.levelToString}</td>
            <td class="align-middle" data-cell="tags">${this.createTags(task)}</td>
            <td class="align-middle" data-cell="users">${task.usersToString}</td>
         `

        if (!this.supportId) {
            htmlContent = htmlContent + `
            <td class="align-middle" data-cell="support">${task.supportGroup ? task.supportGroup.header.fullname : ''}</td>
               <td class="align-middle" data-cell="service">${task.supportGroup ? task.supportGroup.service.name : ''}</td>
            `
        }

        htmlContent = htmlContent + `
            <td class="align-middle" data-cell="createdAt">${task.createdAtToString}</td>
        `

        if (!this.supportId) {
            htmlContent = htmlContent + `
               <td class="align-middle" data-cell="createdBy">${task.createdBy ? task.createdBy.fullname : ''}</td>
            `
        }

        htmlContent = htmlContent + `
            <td class="align-middle text-center">
                <button class="btn btn-danger btn-sm shadow my-1" data-action="delete_task"
                    data-url="/task/${task.id}/delete" title="Supprimer la tâche" data-bs-placement="bottom">
                    <span class="fas fa-trash-alt"></span>
                </button>
            </td>
        `

        rowElt.id = 'task_' + task.id
        rowElt.innerHTML = htmlContent

        this.checkStatus(task, rowElt)

        tbodyElt.insertBefore(rowElt, tbodyElt.firstChild)

        const checkboxToggleStatusElt = rowElt.querySelector('input[data-action="toggle_task_status"]')
        checkboxToggleStatusElt.addEventListener('click', () => {
            this.requestToggleStatusTask(checkboxToggleStatusElt)
        })

        const btnEditElt = rowElt.querySelector('button[data-action="edit_task"]')
        btnEditElt.addEventListener('click', () => {
            this.requestShowTask(btnEditElt)
        })

        const btnDeleteElt = rowElt.querySelector('button[data-action="delete_task"]')
        btnDeleteElt.addEventListener('click', () => this.onClickDeleteTask(btnDeleteElt))

        this.updateCounterTasks(1)

        this.taskModal.hide()
        document.getElementById('js-btn-cancel').click()
    }

    /**
     *
     * @param {HTMLElement} elt
     */
    onClickDeleteTask(elt) {
        this.btnConfirmDeleteElt.dataset.url = elt.dataset.url
        this.confirmDeleteModal.show()
    }

    /**
     * @param {Object} task
     * @returns {string}
     */
    createAlerts(task) {
        let alerts = ''
        if (task.alerts.length > 0) {
            alerts = `<span title="${task.alerts.length} rappel(s)">${task.alerts[0].dateToString}
                <i class="fas fa-bell text-secondary"></i></span>`
        }

        return alerts
    }

    /**
     * @param {Object} task
     * @returns {string}
     */
    createTags(task) {
        let tags = ''
        task.tags.forEach(tag => {
            tags += `<span class="badge bg-${tag.color} text-light me-1">${tag.name}</span>`
        })

        return tags
    }

    /**
     * Affiche la tâche dans le formulaire modal.
     * @param {Object} task
     */
    showTask(task) {
        this.taskForm.showTask(task)
        this.taskModal.show()
    }

    /**
     * @param {number} value
     */
    updateCounterTasks(value) {
        const countTasks = parseInt(this.counterTasksElt.dataset.countTasks) + value
        this.counterTasksElt.dataset.countTasks = countTasks
        this.counterTasksElt.textContent = countTasks.toLocaleString()
    }

    /**
     * Met à jour la ligne <tr> correspondant tâche.
     * @param {Object} task
     */
    editTaskTr(task) {
        const rowElt = document.getElementById('task_' + task.id)
        const supportGroup = task.supportGroup

        if (!rowElt) {
            return console.error('No row task ' + task.id + ' in this page.')
        }

        rowElt.querySelector('td[data-cell="title"]').textContent = task.title
        rowElt.querySelector('td[data-cell="end"]').textContent = task.endToString
        rowElt.querySelector('td[data-cell="level"]').textContent = task.levelToString
        rowElt.querySelector('input[type="checkbox"]').checked = task.status
        rowElt.querySelector('td[data-cell="users"]').textContent = task.usersToString
        rowElt.querySelector('td[data-cell="tags"]').innerHTML = this.createTags(task)
        rowElt.querySelector('td[data-cell="alerts"]').innerHTML = this.createAlerts(task)

        this.checkStatus(task, rowElt)

        if (!this.supportId && supportGroup) {
            rowElt.querySelector('td[data-cell="support"]').textContent = supportGroup.header.fullname
            rowElt.querySelector('td[data-cell="service"]').textContent = supportGroup.service.name
        }

        this.taskModal.hide()
    }

    /**
     * Supprime la ligne <tr> correspondant tâche.
     * @param {Object} task
     * @param {String} action
     */
    deleteTaskTr(task, action) {
        const rowElt = document.getElementById(`task_${task.id}`)

        if (rowElt) {
            rowElt.remove()
        } else {
            console.error('No row task ' + task.id + ' in this page.')
        }

        this.updateCounterTasks(-1)

        this.taskModal.hide()
        document.getElementById('js-btn-cancel').click()
    }

    /**
     * Inverse le statut de la tâche (réalisée/non-réalisée).
     * @param {Object} task
     * @param {HTMLTableRowElement} rowElt
     */
    checkStatus(task, rowElt = null) {
        rowElt = rowElt ?? document.querySelector(`tr#task_${task.id}`)
        const endTdElt = rowElt.querySelector(`td[data-cell="end"]`)
        const spanElt = endTdElt.querySelector('span.fas.fa-exclamation')

        if (task.status === true) {
            rowElt.classList.add('text-secondary', 'delete')
        } else {
            rowElt.classList.remove('text-secondary', 'delete')
        }

        if (spanElt && task.status === false) {
            endTdElt.classList.add('text-danger')
            spanElt.classList.remove('d-none')
            return
        }

        endTdElt.classList.remove('text-danger')

        if (spanElt) {
            spanElt.classList.add('d-none')
        }
    }

    /**
     * @param {number} taskId
     */
    getUrlTaskShow(taskId) {
        return this.modalTaskElt.dataset.urlTaskShow.replace('__id__', taskId)
    }

    /**
     * Redirects if there are no more lines.
     * @param {number} delay
     */
    checkToRedirect(delay) {
        if (document.querySelectorAll('table#table_tasks tbody tr').length === 0) {
            setTimeout(() => {
                document.location.href = location.pathname
            }, delay * 1000)    
        }
    }
}