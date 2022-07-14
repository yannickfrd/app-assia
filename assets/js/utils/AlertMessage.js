import {Toast} from 'bootstrap'
import DateFormater from './date/dateFormater'

export default class AlertMessage {

    constructor(alert = 'info', message, delay = 8) {
        this.alertContainerElt = document.querySelector('.toast-container')
        this.alert = alert
        this.message = message
        this.delay = delay * 1000
        this.alertElt = this.#createAlertElt()
        this.toast = new Toast(this.alertElt)

        this.#init()
    }

    #init() {
        this.alertContainerElt.appendChild(this.alertElt)
        this.toast.show()
    }

    /**
     * @return {HTMLDivElement}
     */
    #createAlertElt() {
        const alertElt = document.createElement('div')
        alertElt.className = 'toast alert-' + this.alert
        alertElt.dataset.bsAutohide = 'true'
        alertElt.dataset.bsDelay = this.delay
        alertElt.setAttribute('aria-live', 'assertive')
        alertElt.setAttribute('aria-atomic', 'true')
        
        alertElt.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <small class="text-muted">${new DateFormater().getTimeNow()}</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${this.message}</div>
        `
        return alertElt
    }
}