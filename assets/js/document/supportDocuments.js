import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import SelectType from '../utils/selectType'
import { Modal } from 'bootstrap'
import Ajax from '../utils/ajax'
import Dropzone from '../utils/file/dropzone'
import CheckboxSelector from '../utils/checkboxSelector'
import DateFormater from '../utils/date/dateFormat'

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
        this.updateBtnElt = this.documentFormElt.querySelector('button[data-action="update"]')
        this.deleteBtnElt = this.documentFormElt.querySelector('button[data-action="delete"]')

        this.modalBlockElt = document.getElementById('modal-block')
        this.deleteModalElt = new Modal(this.modalBlockElt)
        this.confirmDeleteBtnElt = document.getElementById('modal-confirm')

        this.actionFormElt = document.querySelector('form[name="action"]')
            
        this.themeColor = document.getElementById('header').getAttribute('data-color')
        this.countDocumentsElt = document.getElementById('count-documents')

        this.init()
    }

    init() {
        document.querySelector('main').addEventListener('dragenter', () => this.dropzoneModalElt.show())
        document.getElementById('btn-new-files').addEventListener('click', () => this.dropzoneModalElt.show())
        document.querySelectorAll('tr[data-document-id]').forEach(documentTrElt => this.addEventListenersToTr(documentTrElt))
        this.updateBtnElt.addEventListener('click', e => this.requestToUpdate(e))
        this.deleteBtnElt.addEventListener('click', e => {
            this.requestToDelete(e, this.deleteBtnElt.getAttribute('data-document-id'))
        })
        this.confirmDeleteBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.confirmDeleteBtnElt.getAttribute('data-url'), this.responseAjax.bind(this))
        })
        document.getElementById('action-validate').addEventListener('click', e => this.onValidateAction(e))
        
    }

    /**
     * @param {Event} e 
     */
    onValidateAction(e) {
        e.preventDefault()

        const actionTypeSelect = document.getElementById('action_type')
        const option = this.selectType.getOption(actionTypeSelect)
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
        const url = this.actionFormElt.getAttribute('action')
        formData.append('items', JSON.stringify(items))
        // let parameters = '?'
        // items.forEach(item => { parameters += 'items%5B%5D=' + item + '&' })
        // window.location.assign(url + parameters)
        this.ajax.send('POST', url, this.responseAjax.bind(this), formData)
        return new MessageFlash('success', 'Le téléchargement est en cours. Veuillez patienter...')
    }

    /**
     * @param {Array} items 
     */
    deleteFiles(items) {
        this.loader.on()
        const url = this.deleteBtnElt.getAttribute('data-url-document-delete')
        items.forEach(id => {
            this.ajax.send('GET', url.replace('__id__', id), this.responseAjax.bind(this))
        })
    }

    /**
     * @param {File} file
     */
    uploadFile(file = null) {
        const url = this.dropzoneFormElt.getAttribute('action')
        const formData = new FormData(this.dropzoneFormElt)

        if (file) {
            formData.append('files', file)
        }

        this.ajax.send('POST', url, this.responseAjax.bind(this), formData)
    }

    /**
     * Affiche le document sélectionné dans le formulaire modal.
     * @param {Event} e 
     * @param {HTMLTableRowElement} documentTrElt 
     */
    showDocument(e, documentTrElt) {
        if (!e.target.className.includes('cursor-pointer')) {
            return null
        }

        const id = documentTrElt.getAttribute('data-document-id')
        const typeValue = documentTrElt.querySelector('td[data-document="type"]').getAttribute('data-type-value')

        this.documentFormElt.action = this.documentFormElt.getAttribute('data-url').replace('__id__', id)
        this.documentFormElt.querySelector('#document_name').value = documentTrElt.querySelector('td[data-document="name"]').textContent
        this.documentFormElt.querySelector('#document_content').value = documentTrElt.querySelector('td[data-document="content"]').textContent
        this.selectType.setOption(this.documentFormElt.querySelector('#document_type'), typeValue)
        this.deleteBtnElt.classList.replace('d-none', 'd-block')
        this.deleteBtnElt.setAttribute('data-document-id', id)
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
     * @param {String} id
     */
    requestToDelete(e, id) {
        e.preventDefault()

        const url = this.deleteBtnElt.getAttribute('data-url-document-delete').replace('__id__', id)
        const documentName = this.documentFormElt.querySelector('#document_name').value

        this.updateDeleteModal(documentName, url)
    }

    /**
     * @param {Object} response 
     */
    responseAjax(response) {
        switch (response.action) {
            case 'create':
                this.createDocumentTr(response.data)
                break
            case 'update':
                this.updateDocumentTr(response.data)
                break
            case 'delete':
                this.deleteDocumentTr(response.data)
                break
            case 'download':
                return this.getFile(response.data)
                break
        }
        new MessageFlash(response.alert, response.msg)
        this.loader.off()
    }

    /**
     * Crée la ligne du nouveau document dans le tableau.
     * @param {Object} data 
     */
    createDocumentTr(datas) {
        datas.forEach(data => {
            const containerDocumentsElt = document.getElementById('container-documents')
            const documentTrElt = this.getDocumentTrPrototype(data)

            containerDocumentsElt.insertBefore(documentTrElt, containerDocumentsElt.firstChild)

            this.updateCounter(1)
            this.addEventListenersToTr(documentTrElt)
            this.dropzone.updateItemInList(data)
        })
    }   

    /**
     * @param {HTMLTableRowElement} documentTrElt 
     */
    addEventListenersToTr(documentTrElt) {
        documentTrElt.addEventListener('click', e => this.showDocument(e, documentTrElt))
        const deleteBtnElt = documentTrElt.querySelector('button[data-action="delete"]')
        deleteBtnElt.addEventListener('click', () => {
            deleteBtnElt.setAttribute('data-document-id', documentTrElt.getAttribute('data-document-id'))
            const documentName = documentTrElt.querySelector('td[data-document="name"]').textContent
            this.updateDeleteModal(documentName, deleteBtnElt.getAttribute('data-url'))
        })      
    }

    /**
     * @param {String} documentName 
     * @param {String} url 
     */
    updateDeleteModal(documentName, url) {
        const modalBodyElt = this.modalBlockElt.querySelector('div.modal-body')
        modalBodyElt.innerHTML = `<br/><p>Êtes-vous vraiment sûr de vouloir supprimer le document <b>${documentName}</b> ?</p><br/>`
        this.confirmDeleteBtnElt.setAttribute('data-url', url)
        this.deleteModalElt.show()
    }

    /**
     * Met à jour la ligne du tableau correspondant au document.
     * @param {Object} data 
     */
    updateDocumentTr(data) {
        const documentTrElt = document.querySelector(`tr[data-document-id="${data.id}"]`)
        documentTrElt.querySelector('td[data-document="name"]').textContent = data.name
        const documentTypeTdElt = documentTrElt.querySelector('td[data-document="type"]')
        documentTypeTdElt.setAttribute('data-type-value', data.type)
        documentTypeTdElt.textContent = data.typeToString
        documentTrElt.querySelector('td[data-document="content"]').textContent = data.content
        this.documentModalElt.hide()
    }
    /**
     * @param {Object} data 
     */ 
    deleteDocumentTr(data) {
        this.documentModalElt.hide()
        document.querySelector(`tr[data-document-id="${data.id}"]`).remove()
        this.updateCounter(-1)
    }

    /**
     * @param {Object} data 
     */
    getFile(data) {
        this.loader.on()
        this.ajax.showFile(data.file, data.filename)
        this.loader.off()
    }

    /**
     * @param {Object} data 
     */ 
    getDocumentTrPrototype(data) {
        const documentTrElt = document.createElement('tr')
        documentTrElt.setAttribute('data-document-id', data.id)
        documentTrElt.innerHTML =`
            <td scope="row" class="align-middle text-center">
                <div class="custom-control custom-checkbox custom-checkbox-${this.themeColor} text-dark pl-0" 
                    title="Sélectionner le document">
                    <div class="form-check">
                        <input type="checkbox" id="checkbox-file-${data.id}" data-checkbox="${data.id}"
                            name="checkbox-file-${data.id}" class="custom-control-input checkbox form-check-input">
                        <label class="custom-control-label form-check-label ml-2" for="checkbox-file-${data.id}"></label>
                    </div>
                </div>
            </td>
            <td class="align-middle text-center">
                <a href="/document/${data.id}/download" class="btn btn-${this.themeColor} btn-sm shadow my-1" 
                    title="Télécharger le document"><span class="fas fa-file-download"></span>
                </a>
            </td>
            <td class="align-middle cursor-pointer" data-document="name">${data.name}</td>
            <td class="align-middle cursor-pointer" data-document="type" data-type-value="${data.type}">${data.typeToString ?? ''}</td>
            <td class="align-middle cursor-pointer" data-document="content">${data.content ?? ''}</td>
            <td class="align-middle text-right">${((Math.floor(data.size / 10000) / 100).toLocaleString('fr') + ' Mo')}</td>
            <td class="align-middle" data-document="extension">${data.fileType}</td>
            <td class="align-middle">${new DateFormater().getDate(data.createdAt)}</td>
            <td class="align-middle">${data.createdBy.fullname}</td>
            <td class="align-middle text-center">
                <button data-url="/document/${data.id}/delete" class="btn btn-danger btn-sm shadow my-1"
                    data-action="delete" title="Supprimer le document"><span class="fas fa-trash-alt"></span>
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
}