import DocumentManager from "./DocumentManager";
import SelectManager from "../utils/form/SelectManager";

export default class DocumentForm {

    /**
     *  @param {DocumentManager} manager
     */
    constructor(manager) {
        this.manager = manager
        this.loader = manager.loader
        this.ajax = manager.ajax
        this.responseAjax = manager.responseAjax.bind(manager)
        this.modalConfirmation = manager.modalConfirmation 
        this.documentModal = manager.objectModal

        this.formDocumentElt = document.querySelector('form[name=document]')

        this.tagsSelectManager = new SelectManager('#document_tags')
        
        this.init()
    }

    init() {
        this.formDocumentElt.querySelector('button[data-action="update"]').addEventListener('click', e => {
            e.preventDefault()
            this.requestUpdate()
        })

        this.formDocumentElt.querySelector('button[data-action="delete"]').addEventListener('click', e => {
            e.preventDefault()
            this.manager.showModalConfirm()
        })
    }

    /**
     * @param {Object} doc
     */
     show(doc) {
        this.formDocumentElt.querySelector('input[name="document[name]"]').value = doc.name
        this.formDocumentElt.querySelector('textarea[name="document[content]"]').value = doc.content ?? ''

        if (doc.tags !== null) {
            const tagIds = [];
            doc.tags.forEach(tag => tagIds.push(tag.id));
            this.tagsSelectManager.updateItems(tagIds);
        } else {
            this.tagsSelectManager.clearItems()
        }
    }

    requestUpdate() {
        if (this.loader.isActive() === false) {
            this.ajax.send('POST', this.manager.pathEdit(), this.responseAjax, new FormData(this.formDocumentElt))
        }
    }
}