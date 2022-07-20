import FormValidator from "../../utils/form/formValidator";
import SelectManager from "../../utils/form/SelectManager";
import DateFormater from "../../utils/date/dateFormater";
import AlertMessage from "../../utils/AlertMessage";
import {Modal} from "bootstrap";
import RdvModel from "./model/RdvModel";
import ApiCalendar from "../../api/ApiCalendar";
import WidgetCollectionManager from "../../utils/form/WidgetCollectionManager";
import SearchLocation from '../../utils/searchLocation'

export default class RdvForm {
    /**
     * @param {RdvManager|CalendarManager} manager
     */
    constructor(manager) {
        this.manager = manager
        this.loader = manager.loader
        this.ajax = manager.ajax

        this.apiCalendar = new ApiCalendar()

        this.modalRdvElt = document.getElementById('modal-rdv')
        this.rdvModal = new Modal(this.modalRdvElt)

        new SearchLocation('rdv_search_location')

        this.btnAddAlertElt = document.querySelector('button[data-add-widget]')
        this.btnSaveRdvElt = this.modalRdvElt.querySelector('button[data-action="save-rdv"]')
        this.btnDeleteRdvElt = this.modalRdvElt.querySelector('button[data-action="delete-rdv-modal"]')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name="rdv"]')
        this.rdvTitleElt = this.modalRdvElt.querySelector('.modal-header h2')

        this.confirmDeleteModalElt = document.getElementById('modal-block')
        this.confirmDeleteModal = new Modal(this.confirmDeleteModalElt)

        this.supportPeopleElt = document.getElementById('js-support-people')

        this.infoRdvElt = document.getElementById('js-rdv-info')
        this.rdvTitleInput = this.modalRdvElt.querySelector('input[name="rdv[title]"]')
        this.rdvStartInput = this.modalRdvElt.querySelector('input[name="rdv[start]"]')
        this.rdvEndInput = this.modalRdvElt.querySelector('input[name="rdv[end]"]')
        this.rdvLocationInput = this.modalRdvElt.querySelector('input[name="rdv[location]"]')
        this.rdvStatusInput = this.modalRdvElt.querySelector('select[name="rdv[status]"]')
        this.rdvContentText = this.modalRdvElt.querySelector('textarea[name="rdv[content]"]')

        this.usersSelecElt = document.getElementById('rdv_users')

        const divSupportElt = document.querySelector('div[data-support]')
        this.supportId = divSupportElt ? divSupportElt.dataset.support : null

        this.supportSelectElt = document.getElementById('rdv_supportGroup')

        this.dateInput = this.modalRdvElt.querySelector('input[name="date"]')
        this.startInput = this.modalRdvElt.querySelector('input[name="start"]')
        this.endInput = this.modalRdvElt.querySelector('input[name="end"]')

        this.currentUserId = document.getElementById('user-name').dataset.userId

        this.formValidator = new FormValidator(this.formRdvElt)
        this.usersSelectManager = new SelectManager('#rdv_users')
        this.tagsSelectManager = new SelectManager('#rdv_tags')

        this.alertsCollectionManager = new WidgetCollectionManager(this.afterToAddAlert.bind(this), null, 3)

        this.editColumnRdvElt = document.querySelector('table#table-rdvs th[data-path-edit-rdv]')
        this.editContainRdvElt = document.querySelector('div.calendar-table div[data-path-edit-rdv]')

        this.updateModal = new Modal(document.getElementById('modal-update'))

        this.googleCalendarCheckbox = this.modalRdvElt.querySelector('input[name="rdv[_googleCalendar]"]')

        this.outlookCalendarCheckbox = this.modalRdvElt.querySelector('input[name="rdv[_outlookCalendar]"]')
        if (localStorage.getItem('calendar.google') === 'true') {
            this.googleCalendarCheckbox.checked = 'checked'
        }
        if (localStorage.getItem('calendar.outlook') === 'true') {
            this.outlookCalendarCheckbox.checked = 'checked';
        }

        this.rdvBeforeUpdate = null

        this.init()
    }

    init() {
        this.btnSaveRdvElt.addEventListener('click', e => this.requestCreateRdv(e))

        this.pathEditRdv = this.getPathEditRdv()
    }

    /**
     * On click delete btn
     * @param {Event} e
     */
    deleteRdv(e) {
        e.preventDefault()
        this.confirmDeleteModal.show()

        this.confirmDeleteModalElt.querySelector('button#modal-confirm')
            .addEventListener('click', () => this.requestDeleteRdv())
    }

    /**
     * @param {Event} e
     */
    resetForm(e) {
        this.formValidator.reinit()

        this.rdvTitleElt.textContent = 'Nouveau rendez-vous'

        const dateFormater = new DateFormater()
        this.dateInput.value = dateFormater.getDateNow()
        this.startInput.value = dateFormater.getHour()
        const end = parseInt(this.startInput.value.substr(0, 2)) + 1
        this.endInput.value = end + ':00'

        this.infoRdvElt.innerHTML = ''
        this.rdvTitleInput.value = this.supportPeopleElt ? this.supportPeopleElt.querySelector('a').textContent : ''
        this.rdvStartInput.value = ''
        this.rdvEndInput.value = ''
        this.rdvLocationInput.value = ''
        this.rdvStatusInput.value = ''
        this.rdvContentText.value = ''

        this.supportSelectElt.value = this.supportId ?? ''
        this.supportSelectElt.disabled = this.supportId !== null

        this.btnDeleteRdvElt.classList.add('d-none')

        if (e !== undefined && (e.target.className && e.target.className.search('calendar-event') !== 0)) {
            this.rdvModal.show()
            this.tagsSelectManager.clearItems()
        }

        this.usersSelectManager.updateItems(this.currentUserId)

        this.resetAlerts()
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
     * Initialise les rappels du formulaire.
     * @param {Object} rdv
     */
    initAlerts(rdv) {
        this.resetAlerts()

        rdv.alerts.forEach(alert => {
            const alertElt = this.alertsCollectionManager.addElt(this.btnAddAlertElt)
            alertElt.querySelector('input').value = alert.date.slice(0, 19)
            alertElt.querySelector('select').value = alert.type
        })
    }

    /**
     * Définit une date et heure par défaut après l'ajout d'une alerte.
     */
    afterToAddAlert() {
        const elt = this.alertsCollectionManager.listElt.lastElementChild

        const defaultDate = new Date(this.dateInput.value + 'T' + this.startInput.value)
        defaultDate.setDate(defaultDate.getDate() - 1)

        const inputDateElt = elt.querySelector('input')
        inputDateElt.value = new DateFormater().getDate(defaultDate, 'datetimeInput')
        inputDateElt.addEventListener('focusout', e => this.isValidDate(e.target))
    }

    requestCreateRdv() {
        if (!this.isValidForm()) {
            return new AlertMessage('danger', 'Une ou plusieurs informations sont invalides.')
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
     * @param {String} url
     */
    requestShowRdv(url) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', url, this.manager.responseAjax.bind(this.manager));
        }
    }

    requestDeleteRdv() {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send(
                'DELETE',
                this.confirmDeleteModalElt.querySelector('button#modal-confirm').dataset.url,
                this.manager.responseAjax.bind(this.manager)
            )
        }
    }

    /**
     * Show rdv.
     *
     * @param {Object} rdv
     * @param {boolean} canEdit
     */
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
        this.tagsSelectManager.updateItems(tagsIds)

        const userIds = []
        rdv.users.forEach(user => userIds.push(user.id))
        this.usersSelectManager.updateItems(userIds)

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
            this.rdvTitleElt.innerHTML = `<a href="${href}" class="text-primary" title="Accéder au suivi">${title}</a>`
        }

        if (!canEdit) {
            this.btnSaveRdvElt.classList.add('d-none')
            this.btnDeleteRdvElt.classList.add('d-none')
        } else {
            this.btnDeleteRdvElt.classList.remove('d-none')
        }

        this.formRdvElt.action = this.getPathEditRdv().replace('__id__', rdv.id)

        this.confirmDeleteModalElt.querySelector('button#modal-confirm')
            .dataset.url = this.btnDeleteRdvElt.dataset.url.replace('__id__', rdv.id)
        this.btnDeleteRdvElt.addEventListener('click', e => this.deleteRdv(e))

        this.initAlerts(rdv)

        this.rdvModal.show()
    }

    /**
     * Donnes les informations sur l'enregistrement (date de création, créateur...).
     * @param {Object} rdv
     */
    getInfoRdvElt(rdv) {
        let htmlContent = `Créé le ${rdv.createdAtToString} par ${rdv.createdBy.fullname}`
        if (rdv.createdAt !== rdv.updatedAt && rdv.updatedBy !== null) {
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
            tags += `<span class="badge bg-${tag.color} me-1">${tag.name}</span>`
        })

        return tags
    }

    /**
     * @param {Object} rdv
     * @param {Object} apiUrls
     */
    updateApiRdv(rdv, apiUrls) {
        const rdvModel = new RdvModel(rdv)

        if ((this.googleCalendarCheckbox.checked && this.rdvBeforeUpdate.googleEventId === null)
            || (this.outlookCalendarCheckbox.checked && this.rdvBeforeUpdate.outlookEventId === null)
            || (rdvModel.isDifferent(this.rdvBeforeUpdate) && (this.googleCalendarCheckbox.checked
                || this.outlookCalendarCheckbox.checked))
        ) {
            this.updateModal.show()

            const listApis = () => {
                let list = {}

                if (this.googleCalendarCheckbox.checked) {
                    list.google = apiUrls.google;
                }
                if (this.outlookCalendarCheckbox.checked) {
                    list.outlook = apiUrls.outlook
                }

                return Object.keys(list).length === 0 ? apiUrls : list
            }

            document.getElementById('modal-confirm').addEventListener('click', () => {
                this.apiCalendar.addEvent(rdvModel, listApis())
            }, {once: true})
        }
    }

    /**
     *
     * @param {HTMLInputElement} inputDateElt
     * @returns {Boolean}
     */
    isValidDate(inputDateElt) {
        return this.formValidator
            .checkDate(inputDateElt, -(10 * 365), (2 * 365), 'Date incorrecte', false) !== false
    }

    /**
     * Vérifie si les champs du formulaire sont valides.
     * @returns {Boolean}
     */
    isValidForm() {
        let isValid = true
        const fieldElts = [
            this.rdvTitleInput,
            this.dateInput,
            this.startInput,
            this.endInput,
            this.usersSelecElt,
        ]

        this.formRdvElt.classList.add('was-validated')

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

            if (fieldElt.type.includes('date') && this.isValidDate(fieldElt) === false) {
                isValid = false
            }
        })

        return isValid
    }

    /** @returns {String} */
    getPathEditRdv() {
        if (this.editColumnRdvElt !== null) {
            return this.editColumnRdvElt.dataset.pathEditRdv
        } else {
            return this.editContainRdvElt.dataset.pathEditRdv
        }
    }

    closeModal() {
        // The ordre is important
        document.getElementById('js-btn-cancel').click()
        this.rdvModal.hide()
    }
}