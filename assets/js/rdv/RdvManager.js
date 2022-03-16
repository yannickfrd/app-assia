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
        this.modalDeleteElt = new Modal(document.getElementById('modal-block'))

        const divSupportElt = document.querySelector('div[data-support]')
        this.supportId = divSupportElt ? divSupportElt.dataset.support : null

        this.createRdvButton = document.querySelector('button[data-action="add-rdv"]')

        this.rdvModal = new Modal(this.modalRdvElt)
        this.rdvForm = new RdvForm(this)

        this.init()
    }

    init() {
        this.createRdvButton.addEventListener('click', () => this.rdvForm.resetForm())

        document.querySelectorAll('button[data-action="delete-rdv"]').forEach(btnElt => {
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
    onClickDeleteRdv(btnElt) {
        this.modalDeleteElt.show()
        this.modalConfirmElt.dataset.url = btnElt.dataset.url
    }

    /**
     * @param {Object} response
     */
    responseAjax(response) {
        if (response.action) {
            switch (response.action) {
                case 'delete':
                    this.deleteRdv(response.rdvId)
                    break
                case 'create':
                    this.createRdvTr(response.rdv);
                    break;
            }
        }

        new MessageFlash(response.alert, response.msg)
        this.loader.off()
    }

    /**
     * Create rdv's row.
     * @param {Object} rdv
     */
    createRdvTr(rdv) {
        const tbodyElt = document.querySelector('table#table-rdvs tbody')
        const rowElt = document.createElement('tr')

        const rdvTime = (date) => {
            const rdvDate = new Date(date)
            const min = rdvDate.getMinutes().toString().length === 1 ? '0' + rdvDate.getMinutes() : rdvDate.getMinutes()

            return rdvDate.getHours() + ':' + min
        }

        const dateFormater = new DateFormater()
        const createdAt = dateFormater.getDate(rdv.createdAt)
        const dateStart = dateFormater.getDate(rdv.start).split(' ')[0]

        let htmlContent = `
            <td class="align-middle text-center">
                <a href="/rdv/${rdv.id}/show"
                   class="btn btn-${this.themeColor} btn-sm shadow" title="Voir le rendez-vous"
                   data-toggle="tooltip" data-placement="bottom"><i class="fas fa-eye"></i>
                </a>
            </td>
            <td class="align-middle justify"><span class="font-weight-bold">${rdv.title}</span></td>
            <td class="align-middle">${dateStart}<br>${rdvTime(rdv.start)} - ${rdvTime(rdv.end)}
            <td class="align-middle">${rdv.statusToString ?? ''}</td>
            <td class="align-middle">${this.rdvForm.createTags(rdv)}</td>
            <td class="align-middle">${rdv.location ?? ''}</td>
            <td class="align-middle">${rdv.usersToString}</td>`

        if (!this.supportId) {
             htmlContent += `
                <td class="align-middle">${rdv.supportGroup ? rdv.supportGroup.header.fullname : ''}</td>
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

        // const btnEditElt = rowElt.querySelector('button[data-action="edit_task"]')
        // btnEditElt.addEventListener('click', () => {
        //     this.requestShowTask(btnEditElt)
        // })

        const btnDeleteElt = rowElt.querySelector('button[data-action="delete-rdv"]')
        btnDeleteElt.addEventListener('click', () => this.onClickDeleteRdv(btnDeleteElt))

        this.rdvForm.updateCounterTasks(1)

        this.rdvModal.hide()
        document.getElementById('js-btn-cancel').click()
    }

    /**
     * Delete rdv's row.
     * @param {number} rdvId
     */
    deleteRdv(rdvId) {
        document.getElementById('rdv-' + rdvId).remove()
    }
}
