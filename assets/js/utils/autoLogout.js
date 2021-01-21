import { Modal } from 'bootstrap'

/**
 * Système de déconnexion automatique après un laps de temps
 */
export default class AutoLogout {

    constructor(timeout = 30, timeAlert = 5) {
        this.userNameElt = document.getElementById('user-name')
        this.modalElt = new Modal(document.getElementById('modal-autoLogout'))
        this.timerElt = document.getElementById('timer-logout')
        this.sessiontTimerElt = document.getElementById('session-timer')
        this.time = timeout * 60
        this.timeAlert = timeAlert * 60
        this.initTime = this.time
        this.intervalID = null
        this.init()
    }

    init() {
        if (this.userNameElt) {
            this.intervalID = window.setInterval(this.count.bind(this), 1000)
            document.addEventListener('click', this.clearTimer.bind(this))
            document.addEventListener('keydown', this.clearTimer.bind(this))
        }
    }

    /**
     * Compte le temps restant.
     */
    count() {
        this.time--
        this.sessiontTimerElt.textContent = this.getFullTime()
        if (this.time === this.timeAlert) {
            this.modalElt.show();
        }
        if (this.time <= this.timeAlert) {
            this.timerElt.textContent = this.getFullTime()
        }
        if (this.time <= 0) {
            this.deconnection()
        }
    }

    /**
     * Donne le temps.
     */
    getFullTime() {
        const minutes = Math.round((this.time / 60) - 0.5)
        const seconds = this.time - (minutes * 60)

        return minutes.toString().padStart(2, '0') + 'mn ' + seconds.toString().padStart(2, '0') + 's'
    }

    /**
     * Remet à zéro le timer.
     */
    clearTimer() {
        this.time = this.initTime
    }

    /**
     * Déconnection via requête Ajax.
     */
    deconnection() {
        console.log('deconnexion')
        clearInterval(this.intervalID)
        this.clearTimer()
        this.modalElt.hide();
        window.location.assign('/deconnexion')
    }
}