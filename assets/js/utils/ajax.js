import MessageFlash from './messageFlash'

/**
 * Requête AJAX.
 */
export default class Ajax {

    /**
     * @param {Loader} loader 
     * @param {Number} delayError delay in seconds 
     */
    constructor(loader = null, delayError = 20) {
        this.loader = loader
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

        if (this.loader) {
            this.loader.on()
        }

        this.timer()

        await fetch(url, {
            method: method, 
            body: data
        }).then(response => this.getResponse(response, callback) 
        ).catch(error => this.getError(error))
    }

    /**
     * Donne la réponse.
     * @param {Response} response 
     * @param {CallableFunction} callback 
     */
    async getResponse(response, callback) {
        const reader = response.body.getReader()
        const contentLength = +response.headers.get('Content-Length')
        let receivedLength = 0
        const chunks = []

        while(true) {
            const {done, value} = await reader.read()

            if (done) {
                break
            }

            chunks.push(value)
            receivedLength += value.length
            if (contentLength > 0) {
                let msg = Math.round((receivedLength / contentLength) * 100) + ' %'
                console.log('Download...' + msg)
                if (this.loader) {
                    this.loader.updateInfo(msg)
                }
            }
        }

        const chunksAll = new Uint8Array(receivedLength)
        let position = 0
        for(let chunk of chunks) {
            chunksAll.set(chunk, position)
            position += chunk.length
        }

        const result = new TextDecoder("utf-8").decode(chunksAll)

        this.loading = false

        if (this.loader) {
            this.loader.off()
        }

        clearInterval(this.countdownID)

        if (response.status === 403) {
            throw new Error('403 Forbidden access')
        }

        const contentType = response.headers.get('content-type')

        if (contentType && contentType.includes('application/json')) {
            return callback(JSON.parse(result))
        }

        if (contentType && contentType.includes('application')) {
            return callback({
                    'action': 'download',
                    'alert': 'success',
                    'msg': 'Le fichier est téléchargé.',
                    'data': {
                        'filename': response.headers.get('content-name'),
                        'file': new Blob(chunks, {
                            type: contentType,
                            name: 'document_name'
                        }),
                    }
                }
            )
        }

        return callback(result)
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
     * @param {Blob} blob 
     */
    showFile(blob, filename = 'document.pdf', target = '_blank') {
        const file = new File([blob], filename, {
            type: blob.type,
        })

        const data = window.URL.createObjectURL(file)
        const link = document.createElement('a')

        link.href = data
        link.target = target
        link.download = filename
        link.click() // window.location.assign(data)

        setTimeout(function(){
            window.URL.revokeObjectURL(data)
        }, 100)
    }

    /**
     * Donne l'erreur.
     * @param {Error} error 
     */
    getError(error) {       
        this.loading = false

        if (this.loader) {
            this.loader.off()
        }
        
        clearInterval(this.countdownID)
        console.error(error.message)

        if (error.message === '403 Forbidden access') {
            return new MessageFlash('danger', 'Vous n\'avez pas les droits pour effectuer cette action. \nIl est nécessaire d\'être référent du suivi ou administrateur.')
        }

        new MessageFlash('danger', `Une erreur s'est produite (${error.message}).`)
    }
}