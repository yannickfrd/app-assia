import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'

/**
 * Requête AJAX
 */
export default class AjaxRequest {

    constructor(loader = null) {
        this.xhr = new XMLHttpRequest()
        this.loader = loader ?? new Loader()
        this.timeSend = null // Temp pour test
        this.timeResp = null // Temp pour test
    }

    /**
     * Initialisation de la requête AJAX.
     * @param {String} method 
     * @param {String} url 
     * @param {CallableFunction} callback 
     * @param {Bool} async 
     * @param {Object} data 
     */
    send(method = 'GET', url, callback, async = true, data = null) {
        this.timeSend = Date.now() // Temp pour test
        this.xhr.open(method, url, async)

        if (method === 'POST') {
            this.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
        }

        this.xhr.addEventListener('load', this.load.bind(this, callback), {
            once: true
        })
        this.xhr.addEventListener('error', this.error.bind(this, url))
        // this.xhr.onload = this.response.bind(this)
        this.xhr.send(data)
    }

    /**
     * Retourne le résultat de la requête.
     * @param {CallableFunction} callback 
     * @param {String} url 
     */
    load(callback, url) {
        if (this.xhr.status >= 200 && this.xhr.status < 400) {
            this.timeResp = Date.now() // Temp pour test
            let time = (this.timeResp - this.timeSend) / 1000 // Temp pour test
            console.log(`AJAX time: ${time}s`)
            // Appelle la fonction callback en lui passant la réponse de la requête
            return callback(this.parseResponse(this.xhr.responseText))
        }
        console.error('Statut: ' + this.xhr.status + ' ' + this.xhr.statusText + ' ' + url)
        this.loader.off()

        if (this.xhr.status === 403) {
            return new MessageFlash('danger', 'Vous n\'avez pas les droits pour effectuer cette action. \nIl est nécessaire d\'être référent du suivi ou administrateur.')
        }
        return new MessageFlash('danger', `Une erreur s'est produite (${this.xhr.status} ${this.xhr.statusText}).`)
    }

    /**
     * Essaie de parser si JSON.
     * @param {*} response
     */
    parseResponse(response) {
        try {
            return JSON.parse(response);
        } catch (e) {
            return response;
        }
    }

    /**
     * Retourne un message d'erreur en console.
     * @param {String} url 
     */
    error(url) {
        console.error(`Status: ${this.xhr.status} : Server with the URL ${url}`)
    }
}