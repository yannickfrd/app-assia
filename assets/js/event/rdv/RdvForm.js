import RdvManager from "./RdvManager"
import CalendarManager from "./CalendarManager"
import FormValidator from "../../utils/form/formValidator"
import SelectManager from "../../utils/form/SelectManager"
import DateFormatter from "../../utils/date/DateFormatter"
import AlertMessage from "../../utils/AlertMessage"
import {Modal} from "bootstrap"
import RdvModel from "./model/RdvModel"
import ApiCalendar from "../../api/ApiCalendar"
import WidgetCollectionManager from "../../utils/form/WidgetCollectionManager"
import LocationSearcher from '../../utils/LocationSearcher'

export default class RdvForm {
    /**
     * @param {RdvManager|CalendarManager} manager
     */
    constructor(manager) {
        this.manager = manager
        this.loader = manager.loader
        this.ajax = manager.ajax
        this.supportId = manager.supportId
        this.rdvModalElt = manager.modalElt

        // Form fields
        this.rdvFormElt = document.querySelector('form[name="rdv"]')
        this.rdvTitleInput = this.rdvFormElt.querySelector('input[name="rdv[title]"]')
        this.rdvStartInput = this.rdvFormElt.querySelector('input[name="rdv[start]"]')
        this.rdvEndInput = this.rdvFormElt.querySelector('input[name="rdv[end]"]')
        this.dateInput = this.rdvFormElt.querySelector('#date')
        this.startInput = this.rdvFormElt.querySelector('#start')
        this.rdvLocationInput = this.rdvFormElt.querySelector('input[name="rdv[location]"]')
        this.rdvStatusInput = this.rdvFormElt.querySelector('select[name="rdv[status]"]')
        this.rdvContentText = this.rdvFormElt.querySelector('textarea[name="rdv[content]"]')
        this.dateInput = this.rdvFormElt.querySelector('input[name="date"]')
        this.startInput = this.rdvFormElt.querySelector('input[name="start"]')
        this.endInput = this.rdvFormElt.querySelector('input[name="end"]')
        this.googleCalendarCheckbox = this.rdvFormElt.querySelector('input[name="rdv[googleCalendar]"]')
        this.outlookCalendarCheckbox = this.rdvFormElt.querySelector('input[name="rdv[outlookCalendar]"]')
        this.usersSelecElt = document.getElementById('rdv_users')
        this.supportSelectElt = document.getElementById('rdv_supportGroup')

        // Others elements
        this.addAlertBtnElt = document.querySelector('button[data-add-widget]')
        this.saveRdvBtnElt = this.rdvModalElt.querySelector('button[data-action="save"]')
        this.btnDeleteElt = this.rdvModalElt.querySelector('button[data-action="delete"]')
        this.rdvTitleElt = this.rdvModalElt.querySelector('.modal-header h2')
        this.supportPeopleElt = document.getElementById('js-support-people')
        this.infoRdvElt = document.querySelector('p[data-rdv="info"]')

        this.currentUserId = document.getElementById('user-name').dataset.userId

        this.rdvModal = this.manager.objectModal
        this.apiCalendar = new ApiCalendar()
        this.locationSearcher = new LocationSearcher(document.querySelector('[data-location-search]'))
        this.formValidator = new FormValidator(this.rdvFormElt)
        this.usersSelectManager = new SelectManager('#rdv_users')
        this.tagsSelectManager = new SelectManager('#rdv_tags')
        this.alertsCollectionManager = new WidgetCollectionManager(this.#afterToAddAlert.bind(this), null, 3)
        this.updateModal = new Modal('#update_api_modal')

        if (localStorage.getItem('calendar.google') === 'true') {
            this.googleCalendarCheckbox.checked = 'checked'
        }
        if (localStorage.getItem('calendar.outlook') === 'true') {
            this.outlookCalendarCheckbox.checked = 'checked'
        }

        this.initRdv = null

        this.#init()
    }

    #init() {       
        this.dateInput.addEventListener('focusout', () => this.#checkDate())
        this.startInput.addEventListener('input', () => this.#checkStart())
        this.endInput.addEventListener('focusout', () =>  this.#checkEnd())

        this.saveRdvBtnElt.addEventListener('click', e => this.requestCreate(e))

        this.btnDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.manager.showModalConfirm()
        })
    }

    /**
     * @param {Event} e
     */
     new(e) {
         this.#resetForm()

         this.rdvFormElt.action = this.manager.pathCreate()

         const targetElt = e.target

        if (targetElt.classList.contains('calendar-day-block')) {
            this.dateInput.value = targetElt.id
            this.rdvModalElt.querySelector('#rdv_start').value = targetElt.id + 'T00:00'
            this.rdvModalElt.querySelector('#rdv_end').value = targetElt.id + 'T00:00'
        }
    }

    requestCreate() {
        if (!this.#isValidForm()) {
            return new AlertMessage('danger', 'Une ou plusieurs informations sont invalides.')
        }

        if (this.loader.isActive() === false) {
            this.#updateDateTimes()

            this.loader.on()

            const formData = new FormData(this.rdvFormElt)
            this.ajax.send(
                'POST',
                this.rdvFormElt.action,
                this.manager.responseAjax.bind(this.manager),
                formData
            )
        }
    }

    /**
     * @param {Object} rdv
     */
    show(rdv) {
        this.initRdv = rdv
        const title = 'RDV' + (rdv.supportGroup ? ' | ' + rdv.supportGroup.header.fullname : '')
        
        this.rdvTitleElt.textContent = title
        this.infoRdvElt.innerHTML = this.#getInfoRdvElt(rdv)
        this.rdvTitleInput.value = rdv.title
        this.rdvStartInput.value = rdv.start.substr(0, 16)
        this.rdvEndInput.value = rdv.end.substr(0, 16)
        this.dateInput.value = rdv.start.substr(0, 10)
        this.startInput.value = rdv.start.substr(11, 5)
        this.endInput.value = rdv.end.substr(11, 5)
        this.rdvStatusInput.value = rdv.status ? rdv.status : ''
        this.rdvLocationInput.value = rdv.location
        this.locationSearcher.refreshItem(rdv.id, rdv.location)

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
            this.rdvTitleElt.innerHTML = `<a href="${this.manager.pathShowSupport(rdv.supportGroup.id)}" 
                class="text-primary" title="Accéder au suivi">${title}</a>`
        }

        this.rdvFormElt.action = this.manager.pathEdit(rdv.id)

        this.#initAlerts(rdv)
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

        if ((this.googleCalendarCheckbox.checked && this.initRdv.googleEventId === null)
            || (this.outlookCalendarCheckbox.checked && this.initRdv.outlookEventId === null)
            || (rdvModel.isDifferent(this.initRdv) && (this.googleCalendarCheckbox.checked
                || this.outlookCalendarCheckbox.checked))
        ) {
            this.updateModal.show()

            const listApis = () => {
                let list = {}

                if (this.googleCalendarCheckbox.checked) {
                    list.google = apiUrls.google
                }
                if (this.outlookCalendarCheckbox.checked) {
                    list.outlook = apiUrls.outlook
                }

                return Object.keys(list).length === 0 ? apiUrls : list
            }

            document.getElementById('modal_confirm_btn').addEventListener('click', () => {
                this.apiCalendar.addEvent(rdvModel, listApis())
            }, {once: true})
        }
    }

    closeModal() {
        this.rdvModal.hide()
    }

    /**
     * Reinitialize the fields of form.
     */
     #resetForm() {
        this.formValidator.reinit()

        this.rdvTitleElt.textContent = 'Nouveau rendez-vous'

        this.rdvFormElt.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(fieldElt => {
            fieldElt.value = ''
        })

        const dateFormatter = new DateFormatter()
        this.dateInput.value = dateFormatter.getDateNow()
        this.startInput.value = dateFormatter.getHour()
        const end = parseInt(this.startInput.value.substr(0, 2)) + 1
        this.endInput.value = end + ':00'

        this.infoRdvElt.innerHTML = ''
        this.rdvTitleInput.value = this.supportPeopleElt ? this.supportPeopleElt.querySelector('a').textContent : ''

        this.locationSearcher.searchSelect.clear()

        this.supportSelectElt.value = this.supportId ?? ''
        this.supportSelectElt.disabled = this.supportId !== null

        this.btnDeleteElt.classList.add('d-none')

        this.tagsSelectManager.clearItems()

        this.usersSelectManager.updateItems(this.currentUserId)

        this.#resetAlerts()
    }

    /**
     * Initialize the alert elements of form.
     * 
     * @param {Object} rdv
     */
     #initAlerts(rdv) {
        this.#resetAlerts()

        rdv.alerts.forEach(alert => {
            const alertElt = this.alertsCollectionManager.addElt(this.addAlertBtnElt)
            alertElt.querySelector('input').value = alert.date.slice(0, 19)
            alertElt.querySelector('select').value = alert.type
        })
    }

    /**
     * Reinitialize the alert elements of form.
     */
    #resetAlerts() {
        const alertprototype = document.querySelector('#alerts-fields-list')
        alertprototype.innerHTML = ''
        alertprototype.dataset.widgetCounter = 0
        this.addAlertBtnElt.classList.remove('d-none')
    }

    /**
     * Define a default datetime after to add a alert.
     */
    #afterToAddAlert() {
        const elt = this.alertsCollectionManager.listElt.lastElementChild

        const defaultDate = new Date(this.dateInput.value + 'T' + this.startInput.value)
        defaultDate.setDate(defaultDate.getDate() - 1)

        const inputDateElt = elt.querySelector('input')
        inputDateElt.value = new DateFormatter().format(defaultDate, 'datetimeInput')
        inputDateElt.addEventListener('focusout', e => this.#isValidDate(e.currentTarget))
    }

    /**
     * Get the event informations (created at, created by...).
     * 
     * @param {Object} rdv
     */
    #getInfoRdvElt(rdv) {
        let htmlContent = `Créé le ${rdv.createdAtToString} par ${rdv.createdBy.fullname}`
        if (rdv.createdAt !== rdv.updatedAt && rdv.updatedBy !== null) {
            htmlContent += `<br/> (modifié le ${rdv.updatedAtToString} par ${rdv.updatedBy.fullname})`
        }
        return htmlContent
    }

    #checkDate() {
        this.#updateDateTimes()
    }

    #checkStart() {
        if (isNaN(this.startInput.value)) {
            const endHour = parseInt(this.startInput.value.substr(0, 2)) + 1

            this.endInput.value = endHour.toString().padStart(2, '0') + ':' + this.startInput.value.substr(3, 2)
            this.#updateDateTimes()
        }
    }

    #checkEnd() {
        this.#updateDateTimes()
    }

    /**
     * Update the start and end times.
     */
     #updateDateTimes() {
        if (isNaN(this.dateInput.value) && isNaN(this.startInput.value)) {
            this.rdvStartInput.value = this.dateInput.value + 'T' + this.startInput.value
        }
        if (isNaN(this.dateInput.value) && isNaN(this.endInput.value)) {
            this.rdvEndInput.value = this.dateInput.value + 'T' + this.endInput.value
        }
    }

    /**
     * @param {HTMLInputElement} inputDateElt
     * @returns {boolean}
     */
     #isValidDate(inputDateElt) {
        return this.formValidator.checkDate(inputDateElt, -(10 * 365), (2 * 365), null, false) !== false
    }

    /**
     * Check if the form fields are valids.
     * 
     * @returns {boolean}
     */
    #isValidForm() {
        let isValid = true
        const fieldElts = [
            this.rdvTitleInput,
            this.dateInput,
            this.startInput,
            this.endInput,
            this.usersSelecElt,
        ]

        this.rdvFormElt.classList.add('was-validated')

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

            if (fieldElt.type.includes('date') && this.#isValidDate(fieldElt) === false) {
                isValid = false
            }
        })

        return isValid
    }
}