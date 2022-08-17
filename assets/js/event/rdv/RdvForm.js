import AbstractForm from '../../utils/form/AbstractForm'
import AbstractManager from '../../AbstractManager'
import LocationSearcher from '../../utils/LocationSearcher'
import AlertsManager from '../../utils/form/AlertsManager'

export default class RdvForm extends AbstractForm
{
    /**
     * @param {AbstractManager} manager
     */
    constructor(manager) {
        super(manager)

        // Form fields
        this.inputRdvTitle = this.formElt.querySelector('input[name="rdv[title]"]')
        this.inputRdvStart = this.formElt.querySelector('input[name="rdv[start]"]')
        this.inputRdvEnd = this.formElt.querySelector('input[name="rdv[end]"]')
        this.inputDate = this.formElt.querySelector('input[name="date"]')
        this.inputStart = this.formElt.querySelector('input[name="start"]')
        this.inputEnd = this.formElt.querySelector('input[name="end"]')
        this.checkboxGoogleCalendar = this.formElt.querySelector('input[name="rdv[googleCalendar]"]')
        this.checkboxOutlookCalendar = this.formElt.querySelector('input[name="rdv[outlookCalendar]"]')
        this.selectSupportElt = this.formElt.querySelector('#rdv_supportGroup')

        // Others elements
        this.rdvTitleElt = this.modalElt.querySelector('.modal-header h2')
        this.infoRdvElt = document.querySelector('[data-rdv="info"]')

        this.checkboxGoogleCalendar.checked = localStorage.getItem('calendar.google') === 'true'
        this.checkboxGoogleCalendar.dataset.defaultValue = this.checkboxGoogleCalendar.checked
        this.checkboxOutlookCalendar.checked = localStorage.getItem('calendar.outlook') === 'true'
        this.checkboxOutlookCalendar.dataset.defaultValue = this.checkboxOutlookCalendar.checked

        this.initRdv = null

        this.locationSearcher = new LocationSearcher(document.querySelector('[data-location-search]'))
        this.alertsManager = new AlertsManager(this.inputRdvStart, 3)

        this.init()
    }

    init() {       
        this.inputDate.addEventListener('focusout', () => this.#updateDateTimes())
        this.inputStart.addEventListener('input', () => this.#updateDateTimes())
        this.inputEnd.addEventListener('focusout', () =>  this.#updateDateTimes())
    }

    /**
     * @param {Event} e
     */
     new(e) {
        this.resetForm()

        this.rdvTitleElt.textContent = this.getTitleModal(null, 'Nouveau rendez-vous')
        this.infoRdvElt.textContent = this.getCreateUpdateInfo()

        this.inputDate.value = this.dateFormatter.getDateNow()
        this.inputStart.value = this.dateFormatter.getHour()
        this.inputEnd.value = parseInt(this.inputStart.value.substr(0, 2)) + 1 + ':00'
        this.#updateDateTimes()

        const targetElt = e.target

        if (targetElt.classList.contains('calendar-day-block')) {
            this.inputDate.value = targetElt.id
            this.inputRdvStart.value = targetElt.id + 'T00:00'
            this.inputRdvEnd.value = targetElt.id + 'T00:00'
        }

        this.locationSearcher.searchSelect.clear()

        this.alertsManager.reset()

        this.formData = new FormData(this.formElt)
    }

    /**
     * @param {Object} rdv
     */
     show(rdv) {
        this.initForm(rdv)
        this.initRdv = rdv

        this.rdvTitleElt.innerHTML = this.getTitleModal(rdv, 'Rendez-vous')
        this.infoRdvElt.innerHTML = this.getCreateUpdateInfo(rdv)

        this.inputDate.value = rdv.start.substr(0, 10)
        this.inputStart.value = rdv.start.substr(11, 5)
        this.inputEnd.value = rdv.end.substr(11, 5)
        this.#updateDateTimes()

        this.locationSearcher.refreshItem(rdv.id, rdv.location)

        this.alertsManager.init(rdv.alerts)

        this.formData = new FormData(this.formElt)
    }

    /**
     * Try to save the rdv.
     * 
     * @param {Event} e
     */
     requestToSave(e) {
        e.preventDefault()

        this.#updateDateTimes()

        this.formElt.classList.add('was-validated')

        const formData = new FormData(this.formElt)
        formData.append(this.selectSupportElt.name, this.selectSupportElt.value)

        if (this.loader.isActive() === false && this.isValid()) {
            this.ajax.send('POST', this.formElt.action, this.responseAjax, formData)
        }
    }

    /**
     * Update the start and end times.
     */
     #updateDateTimes() {
        if (isNaN(this.inputStart.value)) {
            const endHour = parseInt(this.inputStart.value.substr(0, 2)) + 1
            this.inputEnd.value = endHour.toString().padStart(2, '0') + ':' + this.inputStart.value.substr(3, 2)
        }
        if (isNaN(this.inputDate.value) && isNaN(this.inputStart.value)) {
            this.inputRdvStart.value = this.inputDate.value + 'T' + this.inputStart.value
        }
        if (isNaN(this.inputDate.value) && isNaN(this.inputEnd.value)) {
            this.inputRdvEnd.value = this.inputDate.value + 'T' + this.inputEnd.value
        }
    }
}