// Permet de vérfiier la validité d'une date
export default class CheckDate {

    constructor(dateElt) {
        this.dateElt = dateElt;
        this.date = dateElt.value ? new Date(dateElt.value) : null;
        this.now = new Date();
        this.intervalWithNow = (this.now - this.date) / (24 * 3600 * 1000);
    }

    getIntervalWithNow() {
        return this.intervalWithNow;
    }

    isValid() {
        if (this.date === null) {
            this.dateElt.value = "";
            return;
        }

        if (isNaN(this.intervalWithNow)) {
            console.error("Invalid date !");
            return false;
        }

        if (this.intervalWithNow > (365 * 99) || this.intervalWithNow < -(365 * 20)) {
            console.error("Invalid date !");
            return false;
        }

        return true;
    }

    isValidInterval(maxInterval) {
        if (this.intervalWithNow > maxInterval || this.intervalWithNow < -maxInterval) {
            console.error("Invalid date !");
            return false;
        }

        return true;
    }

    getIntervaltoYears() {
        return this.intervalWithNow / 365;
    }
}