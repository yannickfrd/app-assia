import {Modal} from "bootstrap";
import SelectManager from "../utils/form/SelectManager";
import MessageFlash from "../utils/messageFlash";
import CheckboxSelector from "../utils/form/checkboxSelector";
import Dropzone from "../utils/file/dropzone";

export default class DocumentForm {

    /**
     *  @param {DocumentManager} documentManager
     */
    constructor(documentManager) {
        this.loader = documentManager.loader
        this.ajax = documentManager.ajax
        this.responseAjax = documentManager.responseAjax.bind(documentManager)

        this.tagsSelectManager = new SelectManager('#document_tags', {name: 'onModal', elementId: 'document-modal'})

        this.documentModalElt = new Modal(document.getElementById('document-modal'))
        this.documentFormElt = document.querySelector('form[name=document]')
        this.updateBtnElt = this.documentFormElt.querySelector('button[data-action="update"]')
        this.deleteBtnElt = this.documentFormElt.querySelector('button[data-action="delete"]')

        this.modalBlockElt = document.getElementById('modal-block')
        this.deleteModalElt = new Modal(this.modalBlockElt)
        this.confirmDeleteBtnElt = document.getElementById('modal-confirm')

        this.dropzoneModalElt = new Modal(document.getElementById('dropzone-modal'))
        this.dropzoneFormElt = document.querySelector('form[name="dropzone_document"]')
        this.dropzone = new Dropzone(this.dropzoneFormElt, this.uploadFile.bind(this))

        this.actionFormElt = document.querySelector('form[name="action"]')
        this.checkboxSelector = new CheckboxSelector()

        this.init()
    }

    init() {
        this.updateBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.requestUpdateDocument()
        })

        this.deleteBtnElt.addEventListener('click', e => {
            e.preventDefault()
            const url = this.deleteBtnElt.dataset.urlDocumentDelete
            const documentName = this.documentFormElt.querySelector('input[name="document[name]"]').value

            this.showDeleteModal(documentName, url)
        })

        this.confirmDeleteBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.requestDeleteDocument()
        })

        document.forms['action'].addEventListener('submit', e => {
            e.preventDefault()
            this.onValidateAction()
        })

        document.querySelector('main')
            .addEventListener('dragenter', () => this.showDropZone())

        document.getElementById('btn-new-files')
            .addEventListener('click', () => this.showDropZone())

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
        this.ajax.send('POST', url, this.responseAjax, formData)
    }

    requestUpdateDocument() {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('POST', this.documentFormElt.action, this.responseAjax, new FormData(this.documentFormElt))

        }
    }

    requestDeleteDocument() {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', this.confirmDeleteBtnElt.dataset.url, this.responseAjax)
        }
    }

    /**
     * Use of the term "file" instead of "document" by conflicts with the DOM.
     * @param {Object} doc
     */
    showDocument(doc) {
        this.documentFormElt.action = this.documentFormElt.action.replace('__id__', doc.id)
        this.documentFormElt.querySelector('input[name="document[name]"]').value = doc.name
        this.documentFormElt.querySelector('textarea[name="document[content]"]').value = doc.content ?? ''

        if (doc.tags !== null) {
            const tagIds = [];
            doc.tags.forEach(tag => tagIds.push(tag.id));
            this.tagsSelectManager.updateSelect(tagIds);
        } else {
            this.tagsSelectManager.clearSelect()
        }

        const deleteBtn = this.documentFormElt.querySelector('button[data-action="delete"]')
        deleteBtn.dataset.urlDocumentDelete = deleteBtn.dataset.urlDocumentDelete.replace('__id__', doc.id)
    }

    /**
     * @param {String} documentName
     * @param {String} url
     */
    showDeleteModal(documentName, url) {
        const modalBodyElt = this.modalBlockElt.querySelector('div.modal-body')
        modalBodyElt.innerHTML = `<br/><p>Êtes-vous vraiment sûr de vouloir supprimer le document <b>${documentName}</b> ?</p><br/>`
        this.confirmDeleteBtnElt.dataset.url = url
        this.deleteModalElt.show()
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

    showDropZone() {
        this.dropzone.clearDropzoneContent()
        this.dropzoneModalElt.show()
    }

    /**
     * @param {Array} items
     */
    downloadFiles(items) {
        this.loader.on()

        const formData = new FormData(this.actionFormElt)
        formData.append('items', JSON.stringify(items))

        this.ajax.send('POST', this.actionFormElt.action, this.responseAjax, formData)
        return new MessageFlash('success', 'Le téléchargement est en cours. Veuillez patienter...')
    }

    /**
     * @param {Array} items
     */
    deleteFiles(items) {
        this.loader.on()
        const url = this.deleteBtnElt.dataset.urlDocumentDelete
        items.forEach(id => {
            this.ajax.send('GET', url.replace('__id__', id), this.responseAjax)
        })
    }
}