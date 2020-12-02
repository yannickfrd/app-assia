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

        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            return response.json().then((json) => {
                return callback(json);
            });
        }

        if (contentType && contentType.includes('application/pdf')) {
            return response.blob().then((blob) => {
                return this.showFile(blob)
            });
        }

        return response.text().then((text) => {
            return callback(text);
        });
    }

    /**
     * 
     * @param {Blob} blob 
     */
    showFile(blob){
        // It is necessary to create a new blob object with mime-type explicitly set
        // otherwise only Chrome works like it should
        const file = new File([blob], {type: "application/pdf"})
        // IE doesn't allow using a blob object directly as link href
        // instead it is necessary to use msSaveOrOpenBlob
        if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(file);
            return;
        } 
        // For other browsers: 
        // Create a link pointing to the ObjectURL containing the blob.
        const data = window.URL.createObjectURL(file);
        const link = document.createElement('a');
        link.href = data;
        link.target = '_blank';
        link.download = "document.pdf";
        
        link.click();

        setTimeout(function(){
            // For Firefox it is necessary to delay revoking the ObjectURL
            window.URL.revokeObjectURL(data);
        }, 100);
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