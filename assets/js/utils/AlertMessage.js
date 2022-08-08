import {Toast} from 'bootstrap'
import DateFormatter from './date/DateFormatter'

export default class AlertMessage {

    constructor(alert, message, delay, autohide) {
        this.alertContainerElt = document.querySelector('.toast-container')
        this.alert = alert ?? 'info'
        this.message = message
        this.delay = delay ??  8 * 1000
        this.autohide = autohide ?? true

        this.#init()
    }

    #init() {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = this.alertContainerElt.dataset.prototype
        this.alertElt = wrapper.firstChild

        this.#hydrateAlertElt()

        this.toast = new Toast( this.alertElt)
        
        this.alertContainerElt.appendChild(this.alertElt)

        this.toast.show()

        this.alertElt.querySelector('.btn-close').addEventListener('click', () => {
            setTimeout(() => this.alertElt.remove(), this.delay)
        })
    }

    #hydrateAlertElt() {
        this.alertElt.classList.add('alert-' + this.alert)
        this.alertElt.dataset.bsAutohide = this.autohide
        this.alertElt.dataset.bsDelay = this.delay
        this.alertElt.querySelector('small.text-muted').textContent = new DateFormatter().getTimeNow()
        this.alertElt.querySelector('.toast-body').innerHTML = this.message
    }
}