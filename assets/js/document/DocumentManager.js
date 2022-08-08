import AbstractManager from '../AbstractManager'
import DocumentForm from "./DocumentForm"
import CheckboxSelector from "../utils/form/checkboxSelector"
import DocumentViewer from "./DocumentViewer"
import Dropzone from "../utils/file/dropzone"
import {Modal} from 'bootstrap'
import AlertMessage from '../utils/AlertMessage'

export default class DocumentManager extends AbstractManager {

    constructor() {
        super('document')

        // Additionnal requests
        this.requestDownload = (id) => this.request('download', id)
        this.requestPreview = (id) => this.request('preview', id)
        this.requestUpload = (id) => this.request('preview', id, 'POST')
        this.requestDeleteFiles = (ids) => ids.forEach(id => {
            this.ajax.send('DELETE', this.getPath('delete', id), this.responseAjax.bind(this))
        })

        this.ajax.delayError = 60

        this.formActionElt = document.querySelector('form[name="action"]')
        this.formDropzoneElt = document.querySelector('form[name="dropzone_document"]')

        this.form = new DocumentForm(this)
        this.documentViewer = new DocumentViewer(this)
        this.checkboxSelector = new CheckboxSelector()
        this.dropzoneModal = new Modal('#modal_dropzone')
        this.dropzone = new Dropzone(this.formDropzoneElt, this.uploadFile.bind(this))

        this.init()
    }

    init() {
        document.querySelector('#btn_add_files')?.addEventListener('click', () => this.showDropZone())
        document.querySelector('main').addEventListener('dragenter', () => this.showDropZone())

        this.formActionElt?.addEventListener('submit', e => this.onValidateAction(e))
    }

    /**
     * @param {Array} items
     */
     requestDownloadFiles(items) {
        const formData = new FormData(this.formActionElt)
        formData.append('items', JSON.stringify(items))

        this.ajax.send('POST', this.formActionElt.action, this.responseAjax.bind(this), formData)
        return new AlertMessage('success', 'Le téléchargement est en cours. Veuillez patienter...')
    }

    /**
     * Request to create document
     * @param {File|null} file
     */
     uploadFile(file = null) {
        const url = this.formDropzoneElt.action
        const formData = new FormData(this.formDropzoneElt)

        if (file) {
            formData.append('files', file)
        }
        this.ajax.send('POST', url, this.responseAjax.bind(this), formData)
    }

    showDropZone() {
        this.dropzone.clearDropzoneContent()
        this.dropzoneModal.show()
    }

    /**
     * @param {Event} e 
     */
     onValidateAction(e) {
        e.preventDefault()

        const actionTypeSelect = document.getElementById('action_type')
        const option = parseInt(actionTypeSelect.value)
        const items = this.checkboxSelector.getItems()

        // Check if items are selected
        if (items.length === 0) {
            return new AlertMessage('danger', 'Aucun document n\'est sélectionné.')
        }
        // If 'download' action
        if (option === 1) {
            return this.requestDownloadFiles(items)
        }
        // If 'delete' action
        if (option === 2 && window.confirm('Attention, vous allez supprimer ces documents. Confirmer ?')) {
            return this.requestDeleteFiles(items)
        }
    }
    
    /**
     * Addionnal event listeners to the object element.
     * 
     * @param {HTMLTableRowElement} trElt 
     */
     extraListenersToElt(trElt) {
         const id = trElt.dataset.documentId
        // Download document
        trElt.querySelector('[data-action="download"]').addEventListener('click', () => this.requestDownload(id))
        // Preview document
        trElt.querySelector('[data-action="preview"]').addEventListener('click', () => {
            if ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/) !== null) {
                return this.requestDownload(id)
            }
            this.requestPreview(id)
        })
    }

    /**
     * Use of the var name "doc" instead of "document" by conflicts with the DOM.
     * 
     * @param {Object} response
     */
    responseAjax(response) {
        const doc = response.document

        switch (response.action) {
            case 'create':
                this.afterUpload(response.documents)
                break
            case 'preview':
                this.documentViewer.preview(response.data)
                break
            case 'download':
                return this.getFile(response.data)
        }

        if (doc) {
            this.checkActions(response, doc)

            if(this.objectModal) {
                this.objectModal?.hide()
            }
        }

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }
    }

    /**
     * @param {Array} docs
     */
    afterUpload(docs) {
        docs.forEach(doc => {
            if (doc.id === null) {
                return this.dropzone.updateItemInList(doc, 'warning')
            }

            this.addElt(doc)
            this.dropzone.updateItemInList(doc)
        })
    }
}
