import MessageFlash from './messageFlash'
import Loader from './loader'

/**
 * Requête AJAX.
 */
export default class Ajax {

    constructor(loader = null) {
        this.loader = loader ?? new Loader()
        this.loading = false
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
        ).catch(error => this.error(error))
    }

    /**
     * Envoie un message d'erreur après 10 secondes si pas de réponse du serveur.
     */
    timer() {
        setTimeout(() => {
            if (this.loading === true) {
                this.loading = false
                this.loader.off()
                console.error('Pas de réponse du serveur')
            }
        }, 15000);
    }

    /**
     * Donne la réponse.
     * @param {Response} response 
     * @param {CallableFunction} callback 
     */
    getResponse(response, callback) {
        this.loading = false
        this.loader.off()
        if (response.status === 403) {
            new MessageFlash('Vous n\'avez pas les droits pour effectuer cette action. \nIl est nécessaire d\'être référent du suivi ou administrateur.');
            throw new Error('403 Forbidden access.')
        }

        let finalResponse = null

        switch (response.headers.get('content-type')) {
            case 'application/json':
                finalResponse = response.json()
                break;
            default:
                finalResponse = response.text()
                break;
        }

        finalResponse.then((data) => {
            return callback(data)
        })
    }

    /**
     * Donne l'erreur.
     * @param {String} error 
     */
    getError(error) {
        this.loading = false
        this.loader.off()
        console.error(error)
        new MessageFlash('danger', `Une erreur s'est produite (${error}).`)
    }
}