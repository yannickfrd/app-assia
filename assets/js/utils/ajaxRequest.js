import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'

/**
 * Requête AJAX
 */
export default class AjaxRequest {

    constructor() {
        this.xhr = new XMLHttpRequest()
        this.loader = new Loader()
        this.timeSend = null // Temp pour test
        this.timeResp = null // Temp pour test
    }

    /**
     * Initialisation de la requête AJAX.
     * @param {String} method 
     * @param {String} url 
     * @param {*} callback 
     * @param {Bool} async 
     * @param {Object} data 
     */
    init(method = 'POST', url, callback, async = true, data = null) {
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
     * @param {*} callback 
     * @param {String} url 
     */
    load(callback, url) {
        if (this.xhr.status >= 200 && this.xhr.status < 400) {
            this.timeResp = Date.now() // Temp pour test
            let time = (this.timeResp - this.timeSend) / 1000 // Temp pour test
            console.log('Statut: ' + this.xhr.status + ', Time: ' + time + 's')
            return callback(this.xhr.responseText) // Appelle la fonction callback en lui passant la réponse de la requête
        }
        console.error('Statut: ' + this.xhr.status + ' ' + this.xhr.statusText + ' ' + url)
        this.loader.off()

        if (this.xhr.status === 403) {
            return new MessageFlash('danger', 'Vous n\'avez pas les droits pour effectuer cette action.')
        }
        return new MessageFlash('danger', `Une erreur s'est produite (${this.xhr.status} ${this.xhr.statusText}).`)
    }

    /**
     * Retourne un message d'erreur en console.
     * @param {String} url 
     */
    error(url) {
        console.error(`Status: ${this.xhr.status} : Server with the URL ${url}`)
    }
}