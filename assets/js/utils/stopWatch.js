/**
 * Test de performance d'une fonction ou d'une méthode
 */
export default class StopWatch {

    constructor() {
        this.name = null
        this.startTime = null
    }

    /**
     * 
     * @param {String} name 
     */
    start(name = null) {
        this.startTime = new Date()
        this.name = name
    }

    stop() {
        const time = new Date() - this.startTime
        console.log((this.name ? this.name + ' : ' : '') + time + ' ms.')
    }
}