import AbstractManager from '../AbstractManager'
import AlertMessage from '../utils/AlertMessage'

export default class ExportManager extends AbstractManager {

    constructor() {
        super('export')

        this.formElt = document.querySelector('#accordion_search>form')
        this.ajax.delayError = 30 * 60

        this.init()
    }

    init() {
        document.querySelector('button#count').addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('POST', this.getPath('count'), this.responseAjax.bind(this), new FormData(this.formElt))
        })

        document.querySelector('button#export').addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('POST', this.getPath('new'), this.responseAjax.bind(this), new FormData(this.formElt))
        })
    }

    /**
     * Addionnal event listeners to the object element.
     * 
     * @param {HTMLTableRowElement} trElt 
     */
     extraListenersToElt(trElt) {
        const id = trElt.dataset.exportId
       // Download export
       trElt.querySelector('[data-action="download"]')?.addEventListener('click', () => this.request('download', id))
    }

    /**
     * Actions after Ajax response.
     * 
     * @param {Object} response 
     */
     responseAjax(response) {
        console.log(response)
        switch (response.action) {
            case 'export':
                this.updateElt(response.export)
                break
            case 'create':
                this.create(response.path)
                break
            case 'download':
                return this.getFile(response.data)
        }

        if (response.export) {
            this.checkActions(response, response.export)
        }

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        this.objectModal?.hide()
    }

    /**
     * @param {path} path 
     */
    create(path) {
        this.ajax.send('POST', path, this.responseAjax.bind(this), new FormData(this.formElt))
    }

    /**
     * @param {Object} exportObject
     * @param {HTMLElement} trElt
     */
     extraUpdatesElt(exportObject, trElt) {
        if (exportObject.size > 0) {
            trElt.querySelectorAll('.fas.fa-spinner').forEach(elt => elt.classList.add('d-none'))
            trElt.querySelector('button[data-action="download"]').classList.remove('d-none')
            this.findEltByDataObjectKey(trElt, 'sizeKo').classList.remove('d-none')
            this.findEltByDataObjectKey(trElt, 'delay').classList.remove('d-none')
        } else {
            trElt.querySelectorAll('.fas.fa-spinner').forEach(elt => elt.classList.remove('d-none'))
            trElt.querySelector('button[data-action="download"]').classList.add('d-none')
            this.findEltByDataObjectKey(trElt, 'sizeKo').classList.add('d-none')
            this.findEltByDataObjectKey(trElt, 'delay').classList.add('d-none')
        }
    }
}