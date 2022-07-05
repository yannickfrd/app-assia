import DocumentManager from "./DocumentManager";
import SelectManager from "../utils/form/SelectManager";
import {Modal} from "bootstrap";

export default class DocumentForm {

    /**
     *  @param {DocumentManager} manager
     */
    constructor(manager) {
        this.loader = manager.loader
        this.ajax = manager.ajax
        this.responseAjax = manager.responseAjax.bind(manager)

        this.documentModal = new Modal(document.getElementById('document-modal'))
        this.documentFormElt = document.querySelector('form[name=document]')
        this.updateBtnElt = this.documentFormElt.querySelector('button[data-action="update"]')
        this.deleteBtnElt = this.documentFormElt.querySelector('button[data-action="delete"]')

        this.tagsSelectManager = new SelectManager('#document_tags')

        this.modalBlockElt = document.getElementById('modal-block')
        this.deleteModal = new Modal(this.modalBlockElt)
        this.confirmDeleteBtnElt = document.getElementById('modal-confirm')

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
    }

    /**
     * @param {Object} doc
     */
     showDocument(doc) {
        const pathEdit = document.getElementById('container-documents').dataset.pathEdit
        this.documentFormElt.action = pathEdit.replace('__id__', doc.id)

        this.documentFormElt.querySelector('input[name="document[name]"]').value = doc.name
        this.documentFormElt.querySelector('textarea[name="document[content]"]').value = doc.content ?? ''

        if (doc.tags !== null) {
            const tagIds = [];
            doc.tags.forEach(tag => tagIds.push(tag.id));
            this.tagsSelectManager.updateItems(tagIds);
        } else {
            this.tagsSelectManager.clearItems()
        }

        const deleteBtn = this.documentFormElt.querySelector('button[data-action="delete"]')
        deleteBtn.addEventListener('click', () => {
            this.confirmDeleteBtnElt.dataset.url = deleteBtn.dataset.urlDocumentDelete.replace('__id__', doc.id)
        })

        this.documentModal.show()
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
     * @param {string} documentName
     * @param {string} url
     */
    showDeleteModal(documentName, url) {
        const modalBodyElt = this.modalBlockElt.querySelector('div.modal-body')
        modalBodyElt.innerHTML = `<br/><p>Êtes-vous vraiment sûr de vouloir supprimer le document <b>${documentName}</b> ?</p><br/>`
        this.confirmDeleteBtnElt.dataset.url = url
        this.deleteModal.show()
    }
}