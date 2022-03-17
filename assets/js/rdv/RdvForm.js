import FormValidator from "../utils/form/formValidator";
import SelectManager from "../utils/form/SelectManager";
import DateFormater from "../utils/date/dateFormater";
import MessageFlash from "../utils/messageFlash";
import {Modal} from "bootstrap";

export default class RdvForm {

    /**
     * @param {RdvManager|Calendar} manager
     */
    constructor(manager) {
        this.manager = manager

        this.loader = manager.loader
        this.ajax = manager.ajax
        this.themeColor = manager.themeColor

        this.modalRdvElt = document.getElementById('modal-rdv')
        this.modalElt = new Modal(this.modalRdvElt)
        this.btnSaveRdvElt = this.modalRdvElt.querySelector('button[data-action="save-rdv"]')
        this.btnDeleteRdvElt = this.modalRdvElt.querySelector('button[data-action="delete-rdv-modal"]')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name="rdv"]')
        this.rdvTitleElt = this.modalRdvElt.querySelector('.modal-header h2')

        this.counterRdvsElt = document.querySelector('span#count-rdvs')

        this.infoRdvElt = document.getElementById('js-rdv-info')
        this.rdvTitleInput = this.modalRdvElt.querySelector('input[name="rdv[title]"]')
        this.rdvStartInput = this.modalRdvElt.querySelector('input[name="rdv[start]"]')
        this.rdvEndInput = this.modalRdvElt.querySelector('input[name="rdv[end]"]')
        this.rdvLocationInput = this.modalRdvElt.querySelector('input[name="rdv[location]"]')
        this.rdvStatusInput = this.modalRdvElt.querySelector('select[name="rdv[status]"]')
        this.rdvContentText = this.modalRdvElt.querySelector('textarea[name="rdv[content]"]')

        const divSupportElt = document.querySelector('div[data-support]')
        this.supportId = divSupportElt ? divSupportElt.dataset.support : null

        this.supportSelectElt = document.getElementById('rdv_supportGroup')

        this.dateInput = this.modalRdvElt.querySelector('input[name="date"]')
        this.startInput = this.modalRdvElt.querySelector('input[name="start"]')
        this.endInput = this.modalRdvElt.querySelector('input[name="end"]')

        this.currentUserId = document.getElementById('user-name').dataset.userId

        this.formValidator = new FormValidator(this.modalRdvElt)

        const eventObject = { name: 'onModal', elementId: 'modal-rdv' }
        this.usersSelectManager = new SelectManager('#rdv_users', eventObject, { width: '100%' })
        this.tagsSelectManager = new SelectManager('#rdv_tags', eventObject)

        this.editColumnRdvElement = document.querySelector('table#table-rdvs th[data-path-edit-rdv]')

        this.rdvBeforeUpdate = null

        this.init()
    }

    init() {
        this.btnSaveRdvElt.addEventListener('click', e => this.requestCreateRdv(e))

        this.pathEditRdv = this.getPathEditRdv()
    }

    onClickDeleteRdvModal(e, url) {
            e.preventDefault()
            this.manager.confirmDeleteModal.show()
            this.manager.btnConfirmDeleteModalElt.dataset.url = url
        }

    /** @returns {String} */
    getPathEditRdv() {
        if (this.editColumnRdvElement !== null) {
            return this.editColumnRdvElement.dataset.pathEditRdv
        }
        // else return dans le calendar
    }

    resetForm(e) {
        this.formRdvElt.action = this.manager.createRdvBtn.dataset.url

        this.formValidator.reinit()

        this.rdvTitleElt.textContent = 'Nouveau rendez-vous'

        const dateFormater = new DateFormater()
        this.dateInput.value = dateFormater.getDateNow()
        this.startInput.value = dateFormater.getHour()
        const end = parseInt(this.startInput.value.substr(0, 2)) + 1
        this.endInput.value = end + ':00'

        this.infoRdvElt.innerHTML = ''
        this.rdvStartInput.value = ''
        this.rdvTitleInput.value = ''
        this.rdvEndInput.value = ''
        this.rdvLocationInput.value = ''
        this.rdvStatusInput.value = ''
        this.rdvContentText.value = ''

        this.supportSelectElt.value = this.supportId ?? ''
        this.supportSelectElt.disabled = this.supportId !== null

        this.usersSelectManager.updateSelect(this.currentUserId)

        this.btnDeleteRdvElt.classList.add('d-none')

        if (e !== undefined && (e.target.className && e.target.className.search('calendar-event') !== 0)) {
            this.tagsSelectManager.clearSelect()
            this.modalRdvElt.show()
            const rdvTags = $('#rdv_tags')
            rdvTags.val(null).trigger('change')
        }
    }

    requestCreateRdv() {
        if (this.rdvTitleInput.value === '') {
            return new MessageFlash('danger', 'Le rdv est vide.')
        }

        if (!this.loader.isActive()) {
            this.updateDateTimes()

            this.loader.on()

            const formData = new FormData(this.formRdvElt)
            this.ajax.send(
                'POST',
                this.formRdvElt.action,
                this.manager.responseAjax.bind(this.manager),
                formData
            )
        }
    }

    /**
     * @param {HTMLElement} htmlElt
     */
    requestShowRdv(htmlElt) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', htmlElt.dataset.url, this.manager.responseAjax.bind(this.manager))
        }
    }

    show(rdv, canEdit) {
        this.rdvBeforeUpdate = rdv

        const title = 'RDV' + (rdv.supportGroup ? ' | ' + rdv.supportGroup.header.fullname : '')
        this.rdvTitleElt.textContent = title
        this.infoRdvElt.innerHTML = this.getInfoRdvElt(rdv)

        this.rdvTitleInput.value = rdv.title

        this.rdvStartInput.value = rdv.start.substr(0, 16)
        this.rdvEndInput.value = rdv.end.substr(0, 16)

        this.dateInput.value = rdv.start.substr(0, 10)
        this.startInput.value = rdv.start.substr(11, 5)
        this.endInput.value = rdv.end.substr(11, 5)

        this.rdvStatusInput.value = rdv.status ? rdv.status : ''

        this.rdvLocationInput.value = rdv.location

        const tagsIds = []
        rdv.tags.forEach(tags => tagsIds.push(tags.id))
        this.tagsSelectManager.updateSelect(tagsIds)

        const userIds = []
        rdv.users.forEach(user => userIds.push(user.id))
        this.usersSelectManager.updateSelect(userIds)

        this.supportSelectElt.value = ''
        this.supportSelectElt.disabled = rdv.supportGroup !== null
        if (rdv.supportGroup) {
            this.supportSelectElt.value = rdv.supportGroup.id
            if (this.supportSelectElt.value === '') {
                const optionElt = document.createElement('option')
                optionElt.value = rdv.supportGroup.id
                optionElt.textContent = rdv.supportGroup.header.fullname
                this.supportSelectElt.appendChild(optionElt)
                this.supportSelectElt.value = rdv.supportGroup.id
            }
        }

        this.rdvContentText.value = rdv.content ? rdv.content : ''

        if (rdv.supportGroup) {
            const href = this.rdvTitleElt.dataset.url.replace('__id__', rdv.supportGroup.id)
            this.rdvTitleElt.innerHTML = `<a href="${href}" class="text-${this.themeColor}" title="Accéder au suivi">${title}</a>`
        }

        if (!canEdit) {
            this.btnSaveRdvElt.classList.add('d-none')
            this.btnDeleteRdvElt.classList.add('d-none')
        }

        this.formRdvElt.action = this.getPathEditRdv().replace('__id__', rdv.id)

        const url = this.btnDeleteRdvElt.dataset.url.replace('__id__', rdv.id)
        this.btnDeleteRdvElt.addEventListener('click', e => this.onClickDeleteRdvModal(e, url))

        this.modalElt.show();
    }

    /**
     * Donnes les informations sur l'enregistrement (date de création, créateur...).
     * @param {Object} rdv
     */
    getInfoRdvElt(rdv) {
        let htmlContent = `Créé le ${rdv.createdAtToString} par ${rdv.createdBy.fullname}`
        if (rdv.createdAt !== rdv.updatedAt) {
            htmlContent += `<br/> (modifié le ${rdv.updatedAtToString} par ${rdv.updatedBy.fullname})`
        }
        return htmlContent
    }

    /**
     * Met à jour les dates de début et de fin.
     */
    updateDateTimes() {
        if (isNaN(this.dateInput.value) && isNaN(this.startInput.value)) {
            this.rdvStartInput.value = this.dateInput.value + 'T' + this.startInput.value
        }
        if (isNaN(this.dateInput.value) && isNaN(this.endInput.value)) {
            this.rdvEndInput.value = this.dateInput.value + 'T' + this.endInput.value
        }
    }

    /**
     * @param {Object} rdv
     * @returns {string}
     */
    createTags(rdv) {
        let tags = ''
        rdv.tags.forEach(tag => {
            tags += `<span class="badge bg-${tag.color} text-light mr-1">${tag.name}</span>`
        })

        return tags
    }

    updateCounterTasks(value) {
        const countRdvs = parseInt(this.counterRdvsElt.dataset.countRdvs) + value
        this.counterRdvsElt.dataset.countTasks = countRdvs
        this.counterRdvsElt.textContent = countRdvs.toLocaleString()
    }


    }