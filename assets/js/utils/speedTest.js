import FormValidator from './formValidator'

/**
 * Test de performance d'une fonction ou d'une méthode
 */
export default class SpeedTest {

    constructor(object, nbLoops = 10) {
        this.nbLoops = nbLoops
        this.formValidator = new FormValidator()
        this.start(object)
    }

    /**  
     * Démarre le test
     */
    start(object = null) {
        let totalTime = 0

        for (let i = 0; i < this.nbLoops; i++) {
            let startTime = new Date()
            for (let j = 0; j < 1; j++) {
                this.formValidator.checkForm()
            }
            let time = new Date() - startTime
            totalTime += time
            console.log((i + 1).toString().padStart(2, '0') + ' : ' + time + 'ms')
        }
        console.log('Moyenne : ' + (totalTime / this.nbLoops) + 'ms')
    }
}