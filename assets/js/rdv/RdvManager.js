import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import { Modal } from 'bootstrap'
import RdvForm from "./RdvForm";
import DateFormater from "../utils/date/dateFormater";
import ApiCalendar from "../api/ApiCalendar";
import RdvModel from "./model/RdvModel";

export default class RdvManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.rdvForm = new RdvForm(this)
        this.apiCalendar = new ApiCalendar();

        this.confirmDeleteModal = new Modal(document.getElementById('modal-block'))
        this.btnConfirmDeleteElt = document.querySelector('button#modal-confirm')

        const divSupportElt = document.querySelector('div[data-support]')
        this.supportId = divSupportElt ? divSupportElt.dataset.support : null

        this.newRdvBtn = document.querySelector('button[data-action="add-rdv"]')
        this.editRdvBtn = document.querySelectorAll('table#table-rdvs button[data-action="edit-rdv"]')
        this.deleteRdvBtn = document.querySelectorAll('table#table-rdvs button[data-action="delete-rdv"]')

        this.init()
    }

    init() {
        this.newRdvBtn.addEventListener('click', e => this.rdvForm.resetForm(e))

        this.editRdvBtn.forEach(btnElt => {
            btnElt.addEventListener('click', () => this.getRdv(btnElt))
        })

        this.deleteRdvBtn.forEach(btnElt => {
            btnElt.addEventListener('click', () => this.deleteRdv(btnElt))
        })
    }

    /**
     * On click in edit rdv.
     * @param {HTMLButtonElement} btnElt
     */
    getRdv(btnElt) {
        this.rdvForm.requestShowRdv(btnElt.dataset.url)
    }

    /**
     * @param {HTMLButtonElement} btnElt
     */
    deleteRdv(btnElt) {
        this.btnConfirmDeleteElt.dataset.url = btnElt.dataset.url
        this.confirmDeleteModal.show()

        document.getElementById('modal-block').addEventListener('click', () => {
            this.rdvForm.requestDeleteRdv(btnElt.dataset.url)
        })
    }

    /**
     * @param {Object} response
     */
    responseAjax(response) {
        const rdv = response.rdv
        const apiUrls = response.apiUrls

        if (response.action) {
            switch (response.action) {
                case 'delete':
                    this.deleteRdvTr(response.rdvId, apiUrls)
                    break
                case 'create':
                    this.createRdvTr(rdv, apiUrls)
                    break;
                case 'edit':
                    this.editRdvTr(rdv, apiUrls)
                    break;
                case 'show':
                    if (response.canEdit) {
                        this.showRdv(rdv, response.canEdit);
                    } else {
                        new MessageFlash('danger', 'Vous n\'êtes pas autorisez à voir ce rendez-vous.')
                    }
                    break;
            }
        }

        if (response.msg !== undefined) {
            new MessageFlash(response.alert, response.msg)
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
     * @param {Object} apiUrls
     */
    createRdvTr(rdv, apiUrls) {
        const tbodyElt = document.querySelector('table#table-rdvs tbody')
        const rowElt = document.createElement('tr')

        const dateFormater = new DateFormater()
        const createdAt = dateFormater.getDate(rdv.createdAt)

        const url = this.rdvForm.getPathEditRdv()
            .replace('__id__', rdv.id)
            .replace('edit', 'show')

        let htmlContent = `
            <td class="align-middle text-center">
                <button class="btn btn-${this.rdvForm.themeColor} btn-sm shadow my-1"
                    title="Voir/Modifier le rendez-vous"  data-toggle="tooltip" data-placement="bottom"
                    data-action="edit-rdv" data-url="${url}">
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
        btnEditElt.addEventListener('click', () => this.getRdv(btnEditElt))

        const btnDeleteElt = rowElt.querySelector('button[data-action="delete-rdv"]')
        btnDeleteElt.addEventListener('click', () => this.deleteRdv(btnDeleteElt))

        //v1
        this.apiCalendar.addEvent(new RdvModel(rdv), apiUrls)

        this.rdvForm.closeModal()
    }

    /**
     * Edit rdv's row.
     * @param {Object} rdv
     * @param {Object} apiUrls
     */
    editRdvTr(rdv, apiUrls) {
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

        this.rdvForm.updateApiRdv(rdv, apiUrls)

        this.rdvForm.closeModal()
    }

    /**
     * Delete rdv's row.
     * @param {number} rdvId
     * @param {Object} apiUrls
     */
    deleteRdvTr(rdvId, apiUrls) {
        document.getElementById('rdv-' + rdvId).remove()

        this.apiCalendar.execute('delete', apiUrls)

        this.rdvForm.closeModal()
    }
}
