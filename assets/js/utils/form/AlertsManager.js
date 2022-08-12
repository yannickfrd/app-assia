import FormValidator from './formValidator'
import DateFormatter from '../date/DateFormatter'
import WidgetCollectionManager from './WidgetCollectionManager'

export default class AlertsManager
{
    constructor(targetInputDate, limit = null, delay = 200) {
        this.targetInputDate = targetInputDate // Use this input date to have a default date value for alerts
        this.btnAddAlertElt = document.querySelector('button[data-add-widget]')

        this.formValidator = new FormValidator()
        this.alertsCollectionManager = new WidgetCollectionManager(() => this.afterAdding(), null, limit, delay)
    }

    /**
     * Initialize the alert elements of form.
     * 
     * @param {Array} alerts
     */
    init(alerts) {
        this.reset()

        alerts.forEach(alert => {
            const alertElt = this.alertsCollectionManager.addElt(this.btnAddAlertElt)

            alertElt.querySelector('input').value = alert.date.slice(0, 19)
            alertElt.querySelector('select').value = alert.type
        })
    }

    /**
     * Reinitialize the alert elements of form.
     */
     reset() {
        const containerElt = document.querySelector('#alerts-fields-list')
        containerElt.innerHTML = ''
        containerElt.dataset.widgetCounter = 0
        this.btnAddAlertElt.classList.remove('d-none')
    }

    /**
     * Define a default datetime after to add a alert.
     */
    afterAdding() {
        const elt = this.alertsCollectionManager.listElt.lastElementChild
        const defaultDate = new Date(this.targetInputDate ? this.targetInputDate.value : null)
        const inputDateElt = elt.querySelector('input')

        inputDateElt.value = new DateFormatter().format(defaultDate, 'datetimeInput')
        inputDateElt.addEventListener('focusout', e => this.formValidator.isValidDate(e.target))
    }
}