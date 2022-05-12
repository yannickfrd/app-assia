import Ajax from '../utils/ajax'
import CheckboxSelector from "../utils/form/checkboxSelector";
import DateFormater from '../utils/date/dateFormater'
import DocumentForm from "./DocumentForm";
import Dropzone from "../utils/file/dropzone";
import Loader from '../utils/loader'
import MessageFlash from '../utils/messageFlash'
import TagsManager from '../tag/TagsManager'
import {Modal} from 'bootstrap'

/**
 * Classe de gestion des documents.
 */
export default class DocumentManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader, 60)

        this.documentForm = new DocumentForm(this)

        this.themeColor = document.getElementById('header').dataset.color

        this.tagsManager = new TagsManager()

        this.documentModal =  this.documentForm.documentModal

        this.countDocumentsElt = document.getElementById('count-documents')

        this.actionFormElt = document.querySelector('form[name="action"]')
        this.checkboxSelector = new CheckboxSelector()

        this.btnNewFiles = document.getElementById('btn-new-files')

        this.dropzoneModal = new Modal(document.getElementById('dropzone-modal'))
        this.dropzoneFormElt = document.querySelector('form[name="dropzone_document"]')
        this.dropzone = new Dropzone(this.dropzoneFormElt, this.uploadFile.bind(this))

        this.init()
    }

    init() {
        document.querySelectorAll('table#table-documents tbody button[data-action="restore"]')
            .forEach(restoreBtn => restoreBtn
                .addEventListener('click', () => this.requestToRestore(restoreBtn)))

        document.querySelectorAll('table#table-documents tbody tr')
            .forEach(trElt => this.addEventListenersToTr(trElt))

        if (this.btnNewFiles) {
            this.btnNewFiles.addEventListener('click', () => this.showDropZone()) 
        }

        document.querySelector('main')
            .addEventListener('dragenter', () => this.showDropZone())

        if (document.forms['action']) {
            document.forms['action'].addEventListener('submit', e => {
                e.preventDefault()
                this.onValidateAction()
            })
        }
    }

    /**
     * Request to create document
     * @param {File|null} file
     */
     uploadFile(file = null) {
        const url = this.dropzoneFormElt.action
        const formData = new FormData(this.dropzoneFormElt)

        if (file) {
            formData.append('files', file)
        }
        // const ajax = new AjaxRequest(this.loader, 60)
        this.ajax.send('POST', url, this.responseAjax.bind(this), formData)
    }

    showDropZone() {
        this.dropzone.clearDropzoneContent()
        this.dropzoneModal.show()
    }

    /**
     * req.onprogress = updateProgress;
     */
     onValidateAction() {
        const actionTypeSelect = document.getElementById('action_type')
        const option = parseInt(actionTypeSelect.value)
        const items = this.checkboxSelector.getItems()

        // Check if items are selected.
        if (0 === items.length) {
            return new MessageFlash('danger', 'Aucun document n\'est sélectionné.')
        }
        // If 'download' action
        if (1 === option) {
            return this.downloadFiles(items)
        }
        // If 'delete' action
        if (2 === option && window.confirm('Attention, vous allez supprimer ces documents. Confirmer ?')) {
            return this.deleteFiles(items)
        }
    }

    /**
     * @param {Array} items
     */
    downloadFiles(items) {
        this.loader.on()

        const formData = new FormData(this.actionFormElt)
        formData.append('items', JSON.stringify(items))

        this.ajax.send('POST', this.actionFormElt.action, this.responseAjax.bind(this), formData)
        return new MessageFlash('success', 'Le téléchargement est en cours. Veuillez patienter...')
    }

    /**
     * @param {Array} items
     */
    deleteFiles(items) {
        if (!this.loader.isActive()) {
            this.loader.on()

            const url = document.getElementById('container-documents').dataset.pathDelete
            items.forEach(id => this.ajax.send('GET', url.replace('__id__', id), this.responseAjax.bind(this)))
        }
    }
    
    /**
     * Show document on click on tr and delete document on click on btn delete
     * @param {HTMLTableRowElement} documentTrElt
     */
    addEventListenersToTr(documentTrElt) {
        const url = document.getElementById('container-documents').dataset.pathShow
        const deleteBtnElt = documentTrElt.querySelector('button[data-action="delete"]')

        documentTrElt.querySelectorAll('td.cursor-pointer').forEach(tdElt => {
            tdElt.addEventListener('click', () => {
                this.requestShowRdv(url.replace('__id__', documentTrElt.dataset.documentId))
            })
        })

        if (deleteBtnElt) {
            deleteBtnElt.addEventListener('click', () => {
                deleteBtnElt.dataset.documentId = documentTrElt.dataset.documentId
                const documentName = documentTrElt.querySelector('td[data-cell="name"]').textContent
                this.documentForm.showDeleteModal(documentName, deleteBtnElt.dataset.url)
            })
        }
    }

    /**
     * @param {string} url
     */
    requestShowRdv(url) {
        if (!this.loader.isActive()) {
            this.loader.on()
            this.ajax.send('GET', url, this.responseAjax.bind(this))
        }
    }

    /**
     * @param {HTMLLinkElement} restoreBtn
     */
    requestToRestore(restoreBtn) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', restoreBtn.dataset.url, this.responseAjax.bind(this))
        }
    }

    /**
     * Use of the term "doc" instead of "document" by conflicts with the DOM.
     * @param {Object} response
     */
    responseAjax(response) {
        switch (response.action) {
            case 'create':
                this.createDocumentTr(response.documents)
                break
            case 'show':
                this.showDocument(response.document)
                break
            case 'update':
                this.updateDocumentTr(response.document)
                break
            case 'delete':
                this.deleteDocumentTr(response.document)
                break
            case 'restore':
                this.deleteDocumentTr(response.document)

                this.messageFlash = new MessageFlash(response.alert, response.msg);
                this.checkToRedirect(this.messageFlash.delay)
                break
            case 'download':
                return this.getFile(response.data)
        }

        if (!this.loader.isActive()) {
            this.loader.off()

            if (response.msg && !this.messageFlash) {
                new MessageFlash(response.alert, response.msg);
            }
        }
    }

    /**
     * Crée la ligne du nouveau document dans le tableau.
     * @param documents
     */
    createDocumentTr(documents) {
        documents.forEach(doc => {
            const containerDocumentsElt = document.getElementById('container-documents')
            const documentTrElt = this.getDocumentTrPrototype(doc)

            containerDocumentsElt.insertBefore(documentTrElt, containerDocumentsElt.firstChild)

            this.updateCounter(1)
            this.addEventListenersToTr(documentTrElt)
            this.dropzone.updateItemInList(doc)
        })
    }

    /**
     * @param {Object} doc
     */
    showDocument(doc) {
        this.documentForm.showDocument(doc)
    }

    /**
     * Met à jour la ligne du tableau correspondant au document.
     * @param {Object} doc
     */
    updateDocumentTr(doc) {
        const documentTrElt = document.querySelector(`tr[data-document-id="${doc.id}"]`)

        documentTrElt.querySelector('td[data-cell="name"]').textContent = doc.name
        documentTrElt.querySelector('td[data-cell="content"]').textContent = doc.content

        this.tagsManager.updateTagsContainer(documentTrElt.querySelector('td[data-cell="tags"]'), doc.tags)

        this.documentModal.hide()
    }

    /**
     * @param {Object} documentResponse
     */
    deleteDocumentTr(documentResponse) {
        if (this.documentModal._isShown) {
            this.documentModal.hide()
        }
        document.querySelector(`tr[data-document-id="${documentResponse.id}"]`).remove()
        this.updateCounter(-1)
    }

    /**
     * @param {Object} doc
     */
    getFile(doc) {
        this.loader.on()
        this.ajax.showFile(doc.file, doc.filename)
        this.loader.off()
    }

    /**
     * @param {Object} doc
     */
    getDocumentTrPrototype(doc) {
        const documentTrElt = document.createElement('tr')
        documentTrElt.dataset.documentId = doc.id
        documentTrElt.innerHTML = `
            <td class="align-middle text-center">
                <div class="custom-control custom-checkbox custom-checkbox-${this.themeColor} text-dark pl-0" 
                    title="Sélectionner le document">
                    <div class="form-check">
                        <input type="checkbox" id="checkbox-file-${doc.id}" data-checkbox="${doc.id}"
                            name="checkbox-file-${doc.id}" class="custom-control-input checkbox form-check-input">
                        <label class="custom-control-label form-check-label ml-2" for="checkbox-file-${doc.id}"></label>
                    </div>
                </div>
            </td>
            <td class="align-middle text-center">
                <a href="/document/${doc.id}/download" class="btn btn-${this.themeColor} btn-sm shadow my-1" 
                    title="Télécharger le document"><i class="fas fa-file-download"></i>
                </a>
            </td>
            <td class="align-middle cursor-pointer" data-cell="name">${doc.name}</td>
            <td class="align-middle cursor-pointer" data-cell="tags"></td>
            <td class="align-middle cursor-pointer" data-cell="content">${doc.content ?? ''}</td>
            <td class="align-middle text-right">${((Math.floor(doc.size / 10000) / 100).toLocaleString('fr') + ' Mo')}</td>
            <td class="align-middle">${doc.fileType}</td>
            <td class="d-none d-lg-table-cell align-middle th-date">${new DateFormater().getDate(doc.createdAt)}</td>
            <td class="d-none d-lg-table-cell align-middle th-w-100">${doc.createdBy.fullname}</td>
            <td class="align-middle text-center">
                <button data-url="/document/${doc.id}/delete" class="btn btn-danger btn-sm shadow my-1"
                    data-action="delete" title="Supprimer le document"><i class="fas fa-trash-alt"></i>
                </button>
            </td>`

        return documentTrElt
    }

    /**
     * @param {Number} number
     */
    updateCounter(number) {
        this.countDocumentsElt.textContent = parseInt(this.countDocumentsElt.textContent) + number
    }

    /**
     * Redirects if there are no more lines.
     * @param {number} delay
     */
    checkToRedirect(delay) {
        if (document.querySelectorAll('table#table-documents tbody tr').length === 0) {
            setTimeout(() => {
                document.location.href = location.pathname
            }, delay * 1000)
        }
    }
}
