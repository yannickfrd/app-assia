import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import { Modal } from 'bootstrap'
import RdvForm from "./RdvForm";
import DateFormater from "../utils/date/dateFormater";

export default class RdvManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.themeColor = document.getElementById('header').dataset.color

        this.modalRdvElt = document.getElementById('modal-rdv')
        this.modalConfirmElt = document.getElementById('modal-confirm')
        this.confirmDeleteModal = new Modal(document.getElementById('modal-block'))
        this.btnConfirmDeleteModalElt = document.querySelector('button#modal-confirm')

        const divSupportElt = document.querySelector('div[data-support]')
        this.supportId = divSupportElt ? divSupportElt.dataset.support : null

        this.createRdvBtn = document.querySelector('button[data-action="add-rdv"]')
        this.editRdvBtn = document.querySelectorAll('table#table-rdvs button[data-action="edit-rdv"]')
        this.deleteRdvBtn = document.querySelectorAll('table#table-rdvs button[data-action="delete-rdv"]')

        this.rdvModal = new Modal(this.modalRdvElt)
        this.rdvForm = new RdvForm(this)

        this.init()
    }

    init() {
        this.createRdvBtn.addEventListener('click', () => this.rdvForm.resetForm())

        this.editRdvBtn.forEach(btnElt => {
            btnElt.addEventListener('click', () => this.onClickEditRdv(btnElt))
        })

        this.deleteRdvBtn.forEach(btnElt => {
            btnElt.addEventListener('click', () => this.onClickDeleteRdv(btnElt))
        })

        this.modalConfirmElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.modalConfirmElt.dataset.url, this.responseAjax.bind(this))
        })
    }

    /**
     * @param {HTMLButtonElement} btnElt
     */
    onClickEditRdv(btnElt) {
        this.rdvForm.requestShowRdv(btnElt)
    }

    /**
     * @param {HTMLButtonElement} btnElt
     */
    onClickDeleteRdv(btnElt) {
        this.confirmDeleteModal.show()
        this.modalConfirmElt.dataset.url = btnElt.dataset.url
    }

    /**
     * @param {Object} response
     */
    responseAjax(response) {
        console.log(response)
        if (response.action) {
            switch (response.action) {
                case 'delete':
                    this.deleteRdvTr(response.rdvId)
                    break
                case 'create':
                    this.createRdvTr(response.rdv);
                    break;
                case 'edit':
                    this.editRdvTr(response.rdv);
                    break;
                case 'show':
                    this.showRdv(response.rdv, response.canEdit);
                    break;
            }
        }

        if (response.msg !== undefined) {
            new MessageFlash(response.alert, response.msg);
        }

        this.loader.off()
    }

    /**
     * @param {Object} rdv
     * @param {boolean} canEdit
     */
    showRdv(rdv, canEdit) {
        this.rdvForm.show(rdv, canEdit)
    }

    /**
     * Format date start of rdv.
     * @param {Object} rdv
     * @returns {string}
     */
    rdvDateToString(rdv) {
        const rdvTime = (date) => {
            const rdvDate = new Date(date)
            const min = rdvDate.getMinutes().toString().length === 1 ? '0' + rdvDate.getMinutes() : rdvDate.getMinutes()

            return rdvDate.getHours() + ':' + min
        }

        const dateFormater = new DateFormater()
        const dateStart = dateFormater.getDate(rdv.start).split(' ')[0]

        return `${dateStart}<br>${rdvTime(rdv.start)} - ${rdvTime(rdv.end)}`
    }

    /**
     * Create rdv's row.
     * @param {Object} rdv
     */
    createRdvTr(rdv) {
        const tbodyElt = document.querySelector('table#table-rdvs tbody')
        const rowElt = document.createElement('tr')

        const dateFormater = new DateFormater()
        const createdAt = dateFormater.getDate(rdv.createdAt)

        let htmlContent = `
            <td class="align-middle text-center">
                <button class="btn btn-${this.themeColor} btn-sm shadow my-1"
                    title="Voir/Modifier le rendez-vous"  data-toggle="tooltip" data-placement="bottom"
                    data-action="edit-rdv" data-url="/rdv/${rdv.id}/show">
                    <span class="fas fa-eye"></span>
                </button>
            </td>
            <td class="align-middle justify" data-cell="title"><span class="font-weight-bold">${rdv.title}</span></td>
            <td class="align-middle" data-cell="start">${this.rdvDateToString(rdv)}
            <td class="align-middle" data-cell="status">${rdv.statusToString ?? ''}</td>
            <td class="align-middle" data-cell="tags">${this.rdvForm.createTags(rdv)}</td>
            <td class="align-middle" data-cell="location">${rdv.location ?? ''}</td>
            <td class="align-middle" data-cell="users">${rdv.usersToString}</td>`

        if (!this.supportId) {
             htmlContent += `
                <td class="align-middle" data-cell="support">${rdv.supportGroup ? rdv.supportGroup.header.fullname : ''}</td>
                <td class="align-middle" data-cell="service">${rdv.supportGroup ? rdv.supportGroup.service.name : ''}</td>`
        }

        htmlContent += `<td class="align-middle" data-cell="createdAt">${createdAt}</td>`

         if (!this.supportId) {
             htmlContent += `
                <td class="align-middle">${rdv.createdBy ? rdv.createdBy.fullname : ''}</td>`
         }
        htmlContent += `
            <td class="align-middle text-center">
                <button data-url="/rdv/${rdv.id}/delete"
                        class="btn btn-danger btn-sm shadow my-1" title="Supprimer le rendez-vous"
                        data-action="delete-rdv" data-toggle="tooltip" data-placement="bottom">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>`

        rowElt.id = 'rdv-'+rdv.id
        rowElt.innerHTML = htmlContent

        tbodyElt.insertBefore(rowElt, tbodyElt.firstChild)

        const btnEditElt = rowElt.querySelector('button[data-action="edit-rdv"]')
        btnEditElt.addEventListener('click', () => this.onClickEditRdv(btnEditElt))

        const btnDeleteElt = rowElt.querySelector('button[data-action="delete-rdv"]')
        btnDeleteElt.addEventListener('click', () => this.onClickDeleteRdv(btnDeleteElt))

        this.rdvForm.updateCounterTasks(1)

        this.rdvModal.hide()
        document.getElementById('js-btn-cancel').click()
    }

    /**
     * Edit rdv's row.
     * @param {Object} rdv
     */
    editRdvTr(rdv) {
        const rowElt = document.getElementById('rdv-' + rdv.id)
        const supportGroup = rdv.supportGroup

        if (!rowElt) {
            return console.error('No row task ' + rdv.id + ' in this page.')
        }

        rowElt.querySelector('td[data-cell="title"] span').textContent = rdv.title ?? ''
        rowElt.querySelector('td[data-cell="start"]').innerHTML = this.rdvDateToString(rdv)
        rowElt.querySelector('td[data-cell="status"]').textContent = rdv.statusToString ?? ''
        rowElt.querySelector('td[data-cell="tags"]').innerHTML = this.rdvForm.createTags(rdv)
        rowElt.querySelector('td[data-cell="location"]').innerHTML = rdv.location ?? ''
        rowElt.querySelector('td[data-cell="users"]').textContent = rdv.usersToString ?? ''

        if (!this.supportId && supportGroup) {
            rowElt.querySelector('td[data-cell="support"]').textContent = supportGroup.header.fullname ?? ''
            rowElt.querySelector('td[data-cell="service"]').textContent = supportGroup.service.name ?? ''
        }

        this.rdvModal.hide()
        document.getElementById('js-btn-cancel').click()
    }

    /**
     * Delete rdv's row.
     * @param {number} rdvId
     */
    deleteRdvTr(rdvId) {
        document.getElementById('rdv-' + rdvId).remove()

        this.rdvModal.hide()
        document.getElementById('js-btn-cancel').click()
    }

}
