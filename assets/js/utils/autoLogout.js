import Ajax from './ajax'
import { Modal } from 'bootstrap'

/**
 * Système de déconnexion automatique après un laps de temps
 */
export default class AutoLogout {

    constructor(timeout = 30, timeAlert = 5) {
        this.ajax = new Ajax()
        this.userNameElt = document.getElementById('user-name')
        this.modalElt = new Modal(document.getElementById('modal-autoLogout'))
        this.timerElt = document.getElementById('timer-logout')
        this.time = timeout * 60
        this.timeAlert = timeAlert * 60
        this.initTime = this.time
        this.intervalID = null
        this.init()
    }

    init() {
        if (this.userNameElt) {
            this.intervalID = window.setInterval(this.count.bind(this), 1000)
        }
    }

    /**
     * Compte le temps restant.
     */
    count() {
        console.log(this.time);
        this.time--
        if (this.time === this.timeAlert) {
            this.modalElt.show();
            ['click', 'keydown'].forEach(eventType =>
                document.addEventListener(eventType, this.clearTimer.bind(this))
            );
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
        let minutes = Math.round((this.time / 60) - 0.5)
        let seconds = this.time - (minutes * 60)

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
        clearInterval(this.intervalID)
        this.ajax.send('GET', '/deconnexion', this.reloadPage.bind(this))
    }

    /**
     * Recharge la page.
     */
    reloadPage() {
        document.location.assign('/login')
    }
}