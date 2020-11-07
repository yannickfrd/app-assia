import MessageFlash from './messageFlash'
import Loader from './loader'

/**
 * Requête AJAX.
 */
export default class Ajax {

    /**
     * @param {Object} loader 
     * @param {Numer} delayError in seconds 
     */
    constructor(loader = null, delayError = 20) {
        this.loader = loader ?? new Loader()
        this.loading = false
        this.delayError = delayError
        this.countdownID = null
        // this.timeSend = null // Temp pour test
        // this.timeResp = null // Temp pour test
    }

    /**
     * Envoie la requête Ajax.
     * @param {String} method 
     * @param {String} url 
     * @param {CallableFunction} callback 
     * @param {Object} data 
     * @param {Bool} async 
     */
    async send(method = 'GET', url, callback, data = null) {
        this.loading = true
        this.loader.on()
        this.timer()
        await fetch(url, {
            method: method, 
            body: data
        }).then(response => this.getResponse(response, callback) 
        ).catch(error => this.getError(`Une erreur s'est produite (${error}).`))
    }

    /**
     * Envoie un message d'erreur après 10 secondes si pas de réponse du serveur.
     */
    timer() {
        this.countdownID = setTimeout(() => {
            if (this.loading === true) {
                this.getError('Pas de réponse du serveur. Veuillez réessayer.')
            }
        }, this.delayError * 1000)
    }

    /**
     * Donne la réponse.
     * @param {Response} response 
     * @param {CallableFunction} callback 
     */
    getResponse(response, callback) {
        this.loading = false
        this.loader.off()
        clearInterval(this.countdownID)
        if (response.status === 403) {
            new MessageFlash('Vous n\'avez pas les droits pour effectuer cette action. \nIl est nécessaire d\'être référent du suivi ou administrateur.')
            throw new Error('403 Forbidden access.')
        }

        let finalResponse = null
        const contentType = response.headers.get('content-type')

        if (contentType && contentType.indexOf("application/json") !== -1) {
            finalResponse = response.json()
        } else {
            finalResponse = response.text()
        }

        finalResponse.then((data) => {
            return callback(data)
        })
    }

    /**
     * Donne l'erreur.
     * @param {String} msg 
     */
    getError(msg) {
        this.loading = false
        this.loader.off()
        clearInterval(this.countdownID)
        console.error(msg)
        new MessageFlash('danger', msg)
    }
}