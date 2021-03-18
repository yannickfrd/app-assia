import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import SelectType from '../utils/selectType'
import { Modal } from 'bootstrap'
import Ajax from '../utils/ajax'
import Dropzone from '../utils/file/dropzone'
import CheckboxSelector from '../utils/checkboxSelector'

/**
 * Classe de gestion des documents.
 */
export default class SupportDocuments {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader, 60)
        this.selectType = new SelectType()
        this.checkboxSelector = new CheckboxSelector()

        this.dropzoneModalElt = new Modal(document.getElementById('dropzone-modal'))
        this.dropzoneFormElt = document.querySelector('form[name="dropzone_document"]')
        this.dropzone = new Dropzone(this.dropzoneFormElt, this.uploadFile.bind(this))
        
        this.documentModalElt = new Modal(document.getElementById('document-modal'))
        this.documentFormElt = document.querySelector('form[name=document]')
        this.documentNameInput = this.documentFormElt.querySelector('#document_name')
        this.documentTypeInput = this.documentFormElt.querySelector('#document_type')
        this.documentContentInput = this.documentFormElt.querySelector('#document_content')
        this.updateBtnElt = this.documentFormElt.querySelector('#js-btn-update')
        this.deleteBtnElt = this.documentFormElt.querySelector('#modal-btn-delete')

        this.modalDeleteElt = new Modal(document.getElementById('modal-block'))
        this.modalConfirmElt = document.getElementById('modal-confirm')

        this.actionFormElt = document.querySelector('form[name="action"]')
            
        this.themeColor = document.getElementById('header').getAttribute('data-color')
        this.countDocumentsElt = document.getElementById('count-documents')
        this.supportId = document.getElementById('container-documents').getAttribute('data-support')

        this.init()
    }

    init() {
        document.querySelector('main').addEventListener('dragenter', () => this.dropzoneModalElt.show())
        document.getElementById('btn-new-files').addEventListener('click', () => this.dropzoneModalElt.show())
        document.querySelectorAll('tr[data-document-id]').forEach(trDocumentElt => this.addEventListenersToTr(trDocumentElt))
        this.updateBtnElt.addEventListener('click', e => this.requestToUpdate(e))
        this.deleteBtnElt.addEventListener('click', e => this.requestToDelete(e, this.deleteBtnElt.href))
        this.modalConfirmElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.modalConfirmElt.getAttribute('data-url'), this.responseAjax.bind(this))
        })
        document.getElementById('action-validate').addEventListener('click', e => this.onValidateAction(e))
    }

    /**
     * @param {Event} e 
     */
    onValidateAction(e) {
        e.preventDefault()
        this.loader.on()
        const actionTypeSelect = document.getElementById('action_type')
        if (window.confirm(`Confirmer cette action ?`)) {
            const selectedCheckboxes = this.checkboxSelector.getSelectedCheckboxes()
            const optionElt = this.selectType.getOptionElt(actionTypeSelect)
            console.log(selectedCheckboxes.length)
            this.ajax.send('GET', optionElt.getAttribute('data-url'), this.responseAjax.bind(this))
        } 
    }

    /**
     * @param {File} file
     */
    uploadFile(file) {
        if (file) {
            const formData = new FormData(this.documentFormElt)
            const url = this.dropzoneFormElt.getAttribute('action')
            formData.append('file', file)
            this.ajax.send('POST', url, this.responseAjax.bind(this), formData)
        }
    }

    /**
     * Affiche le document sélectionné dans le formulaire modal.
     * @param {Event} e 
     * @param {HTMLTableRowElement} trDocumentElt 
     */
    showDocument(e, trDocumentElt) {
        if (e.target.localName != 'td') {
            return null
        }

        this.trDocumentElt = trDocumentElt
        this.contentDocumentElt = trDocumentElt.querySelector('[data-document="content"]')
        this.documentId = trDocumentElt.getAttribute('data-document-id')
        this.documentFormElt.action = `/document/${this.documentId}/edit`

        this.nameDocumentElt = trDocumentElt.querySelector('[data-document="name"]')
        this.documentNameInput.value = this.nameDocumentElt.textContent

        const typeValue = trDocumentElt.querySelector('[data-document="type"]').getAttribute('data-value')
        this.selectType.setOption(this.documentTypeInput, typeValue)

        this.contentDocumentElt = trDocumentElt.querySelector('[data-document="content"]')
        this.documentContentInput.value = this.contentDocumentElt.textContent

        this.deleteBtnElt.classList.replace('d-none', 'd-block')
        this.deleteBtnElt.href = `/document/${this.documentId }/delete`
        this.documentModalElt.show()
    }
   
    /**
     * @param {Event} e
     */
    requestToUpdate(e) {
        e.preventDefault()
        this.loader.on()
        const formData = new FormData(this.documentFormElt)
        const url = this.documentFormElt.getAttribute('action')
        this.ajax.send('POST', url, this.responseAjax.bind(this), formData)
        this.documentModalElt.hide()
    }

    /**
     * @param {Event} e
     * @param {String} url
     */
    requestToDelete(e, url) {
        e.preventDefault()
        this.loader.on()
        if (window.confirm('Voulez-vous vraiment supprimer ce document ?')) {
            this.ajax.send('GET', url, this.responseAjax.bind(this))
        }
    }

    /**
     * @param {Object} response 
     */
    responseAjax(response) {     
        if (response.code === 200) {
            switch (response.action) {
                case 'create':
                    this.createTrDocument(response.data)
                    break
                case 'update':
                    this.updateTrDocument(response.data)
                    break
                case 'delete':
                    this.deleteTrDocument()
                    break
                }
            }
            new MessageFlash(response.alert, response.msg)
            this.loader.off()
    }

    /**
     * Crée la ligne du nouveau document dans le tableau.
     * @param {Object} data 
     */
    createTrDocument(datas) {
        datas.forEach(data => {
            const containerDocumentsElt = document.getElementById('container-documents')
            const trDocumentElt = this.getTrDocumentPrototype(data)

            containerDocumentsElt.insertBefore(trDocumentElt, containerDocumentsElt.firstChild)

            this.updateCounter(1)
            this.addEventListenersToTr(trDocumentElt)
            this.dropzone.updateItemInList(data)
        })
    }

    /**
     * @param {HTMLTableRowElement} trDocumentElt 
     */
    addEventListenersToTr(trDocumentElt) {
        trDocumentElt.addEventListener('click', e => this.showDocument(e, trDocumentElt))
        const btnDeleteElt = trDocumentElt.querySelector('button[data-action="delete"]')
        btnDeleteElt.addEventListener('click', () => {
            this.modalDeleteElt.show()
            this.documentId = btnDeleteElt.parentElement.parentElement.getAttribute('data-document-id')
            this.modalConfirmElt.setAttribute('data-url', btnDeleteElt.getAttribute('data-url'))
        })      
    }

    /**
     * Met à jour la ligne du tableau correspondant au document.
     * @param {Object} data 
     */
    updateTrDocument(data) {
        this.nameDocumentElt.textContent = this.documentNameInput.value
        const documentTypeInput = this.trDocumentElt.querySelector('[data-document="type"')
        documentTypeInput.textContent = data.type
        documentTypeInput.setAttribute('data-value', this.selectType.getOption(this.documentTypeInput))
        this.trDocumentElt.querySelector('[data-document="content"').textContent = this.documentContentInput.value
        this.documentModalElt.hide()
    }


    deleteTrDocument() {
        document.querySelector(`tr[data-document-id="${this.documentId}"]`).remove()
        this.updateCounter(-1)
    }
    /**
     * @param {Object} data 
     */ 
    getTrDocumentPrototype(data) {
        const trDocumentElt = document.createElement('tr')
        trDocumentElt.setAttribute('data-document-id', data.id)
        trDocumentElt.innerHTML =`
            <td scope="row" class="align-middle text-center">
                <div class="custom-control custom-checkbox custom-checkbox-{{ theme_color }} text-dark pl-0" 
                    title="Sélectionner le document">
                    <div class="form-check">
                        <input type="checkbox" id="checkbox-file-{{ document.id }}" data-checkbox="{{ document.id }}"
                            name="checkbox-file-{{ document.id }}" class="custom-control-input checkbox form-check-input">
                        <label class="custom-control-label form-check-label ml-2" for="checkbox-file-{{ document.id }}"></label>
                    </div>
                </div>
            </td>
            <td class="align-middle text-center">
                <a href="/document/${data.id}/read" class="btn btn-${this.themeColor} btn-sm shadow my-1" 
                    title="Télécharger le document"><span class="fas fa-file-download"></span>
                </a>
            </td>
            <td class="align-middle" data-document="name">${data.name}</td>
            <td class="align-middle" data-document="type" data-value=""></td>
            <td class="align-middle" data-document="content"></td>
            <td class="align-middle text-right" data-document="size">${Math.floor(data.size / 10000) / 100 + ' Mo'}</td>
            <td class="align-middle" data-document="extension">${data.extension}</td>
            <td class="align-middle" data-document="createdAt">${data.createdAt}</td>
            <td class="align-middle" data-document="createdBy">${data.createdBy}</td>
            <td class="align-middle text-center">
                <button data-url="/document/${data.id}/delete" class="btn btn-danger btn-sm shadow my-1"
                    data-action="delete" title="Supprimer le document"><span class="fas fa-trash-alt"></span>
                </button>
            </td>`
        
        return trDocumentElt
    }

    /**
     * @param {Number} number 
     */
    updateCounter(number) {
        this.countDocumentsElt.textContent = parseInt(this.countDocumentsElt.textContent) + number
    }    
}