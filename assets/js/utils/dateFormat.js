export default class DateFormat {

    constructor() {
        this.now = new Date();
    }

    // Donne la date actuelle
    getDateNow() {
        let month = this.now.getMonth() + 1;
        if (this.now.getMonth() < 10) {
            month = "0" + month;
        }
        let day = this.now.getDate();
        if (this.now.getDate() < 10) {
            day = "0" + day;
        }
        return this.now.getFullYear() + "-" + month + "-" + day;
    }

    // Donne l'heure et les minutes actuelles
    getTimeNow() {
        let hour = this.now.getHours();
        if (this.now.getHours() < 10) {
            hour = "0" + hour;
        }
        let minutes = this.now.getMinutes();
        if (this.now.getMinutes() < 10) {
            minutes = "0" + minutes;
        }
        return hour + ":" + minutes;
    }

    // Donne l'heure actuelle
    getHour() {
        let hour = this.now.getHours();
        if (this.now.getHours() < 10) {
            hour = "0" + hour;
        }

        return hour + ":00";
    }
}