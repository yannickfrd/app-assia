// Système de déconnexion automatique après un laps de temps

export default class AutoLogout {

    constructor(ajaxRequest, timeout) {
        this.ajaxRequest = ajaxRequest;
        this.userNameElt = document.getElementById("user-name");
        this.modalElt = $("#modal-autoLogout");
        this.timerElt = document.getElementById("timer-logout");
        this.cancelLogoutElt = document.getElementById("cancel-logout");
        this.time = timeout * 60;
        this.initTime = this.time;
        this.intervalID = null;
        this.init();
    }

    init() {
        if (this.userNameElt) {
            this.intervalID = window.setInterval(this.count.bind(this), 1000);
            this.cancelLogoutElt.addEventListener("click", this.clearTimer.bind(this));
            document.addEventListener("click", this.clearTimer.bind(this));
            document.addEventListener("keydown", this.clearTimer.bind(this));
        }
    }

    // Compte le temps restant
    count() {
        this.time--;
        if (this.time === 120) {
            this.modalElt.modal("show");
        }
        if (this.time <= 120) {
            this.timerElt.textContent = this.getFullTime();
        }
        if (this.time <= 0) {
            this.deconnection();
        }
    }

    // Donne le temps
    getFullTime() {
        let minutes = Math.round((this.time / 60) - 0.5);
        let seconds = this.time - (minutes * 60);
        if (minutes < 10) {
            minutes = "0" + minutes;
        };
        if (seconds < 10) {
            seconds = "0" + seconds;
        };
        return minutes + "mn " + seconds + "s";
    }

    // Remet à zéro le timer
    clearTimer() {
        this.time = this.initTime;
    }

    // Deconnexion vi requête Ajax
    deconnection() {
        clearInterval(this.intervalID);
        this.ajaxRequest.init("GET", "/deconnexion", this.reloadPage.bind(this), true);
    }

    // Recharge la page
    reloadPage() {
        document.location.reload(true);
    }
}