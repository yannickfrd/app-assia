import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'

/**
 * Requête Ajax with XMLHttpRequest.
 */
export default class AjaxRequest {

    constructor(loader = null) {
        this.xhr = new XMLHttpRequest()
        this.loader = loader ?? new Loader()
        // this.timeSend = null // Temp pour test
        // this.timeResp = null // Temp pour test
    }

    /**
     * Initialisation de la requête AJAX.
     * @param {String} method 
     * @param {String} url 
     * @param {CallableFunction} callback 
     * @param {Object} data 
     */
    send(method = 'GET', url, callback, data = null) {
        // this.timeSend = Date.now() // Temp pour test
        this.xhr.open(method, url, true)
        // if (method === 'POST') {
        //     this.xhr.setRequestHeader('Content-Type', 'application/octet-stream')
        // }
        this.xhr.onprogress = e => this.inProgress(e)
        this.xhr.onload = () => this.load(callback)
        this.xhr.onerror = () => this.error(url)
        this.xhr.send(data)
    }

    /**
     * @param {ProgressEvent} e 
     */
    inProgress(e) {
        const percentCompleted = Math.round((e.loaded / e.total) * 100);
        console.log('Upload... ' + percentCompleted + ' %');
    }

    /**
     * Retourne le résultat de la requête.
     * @param {CallableFunction} callback 
     */
    load(callback) {
        if (this.xhr.status >= 200 && this.xhr.status < 400) {
            // this.timeResp = Date.now() // Temp pour test
            // let time = (this.timeResp - this.timeSend) / 1000 // Temp pour test
            // console.log(`AJAX time: ${time}s`)
            // Appelle la fonction callback en lui passant la réponse de la requête
            return callback(this.parseResponse(this.xhr.responseText))
        }
        
        if (this.xhr.status === 403) {
            return new MessageFlash('danger', 'Vous n\'avez pas les droits pour effectuer cette action. \nIl est nécessaire d\'être référent du suivi ou administrateur.')
        }
        
        new MessageFlash('danger', `Une erreur s'est produite (${this.xhr.status} ${this.xhr.statusText}).`)
        this.loader.off()
        throw new Error (`Statut: ${this.xhr.status} ${this.xhr.statusText}`)
    }

    /**
     * Essaie de parser si JSON.
     * @param {*} response
     */
    parseResponse(response) {
        try {
            return JSON.parse(response)
        } catch (e) {
            return response
        }
    }

    /**
     * Retourne un message d'erreur en console.
     * @param {String} url 
     */
    error(url) {
       throw new Error(`Status: ${this.xhr.status} : Server with the URL ${url}`)
    }
}