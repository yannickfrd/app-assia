// Permet de retourner une date au format texte
export default class DateFormat {

    constructor() {
        this.now = new Date()
    }

    // Donne la date actuelle
    getDateNow() {
        let month = this.now.getMonth() + 1

        return this.now.getFullYear() + '-' + month.toString().padStart(2, '0') + '-' + this.now.getDate().toString().padStart(2, '0')
    }

    // Donne l'heure et les minutes actuelles
    getTimeNow() {

        return this.now.getHours().toString().padStart(2, '0') + ':' + this.now.getMinutes().toString().padStart(2, '0')
    }

    // Donne l'heure actuelle
    getHour() {

        return this.now.getHours().toString().padStart(2, '0') + ':00'
    }
}