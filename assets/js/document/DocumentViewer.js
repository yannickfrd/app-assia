import DocumentManager from "./DocumentManager";
import {Modal} from 'bootstrap'

export default class DocumentViewer {

    /**
     *  @param {DocumentManager} manager
     */
     constructor(manager) {
        this.manager = manager
        this.loader = manager.loader
        this.ajax = manager.ajax
        this.responseAjax = manager.responseAjax.bind(manager)

        this.previewModalElt = document.getElementById('document_preview_modal')
        this.modalContent = document.querySelector('#document_preview_modal .modal-content')
        this.btnEditElt = this.previewModalElt.querySelector('.modal-footer-preview button[data-action="edit"]')
        this.btnDownloadElt = this.previewModalElt.querySelector('.modal-footer-preview button[data-action="download"]')

        this.previewModal = new Modal(this.previewModalElt)

        this.file = null
        this.documentExtension = null

        this.init()
    }

    init() {
        this.btnDownloadElt.addEventListener('click', e => this.#requestDownload(e))
        this.btnEditElt.addEventListener('click', () => this.manager.requestShow(this.manager.objectId))

        const modalDialogElt = this.previewModalElt.querySelector('.modal-dialog')
        const modalFooterElt = this.previewModalElt.querySelector('.modal-footer-preview')
        modalDialogElt.addEventListener('mouseover', () => modalFooterElt.style.opacity = '1')
        modalDialogElt.addEventListener('mouseout', () => modalFooterElt.style.opacity = '.6')
    }

    /**
     * @param {string} url
     */
     requestPreview(id) {
        if (this.loader.isActive() === false) {
            this.ajax.send('GET', url, this.responseAjax)
        }
    }

    /**
     * @param {Event} e 
     */
     #requestDownload(e) {
        e.preventDefault()
        if (['doc', 'docx', 'txt'].includes(this.documentExtension)) {
            if(this.loader.isActive() === false) {
                this.loader.on()
                this.ajax.send('GET', this.btnDownloadElt.dataset.path, this.responseAjax)
            }
            return
         }

        const data = window.URL.createObjectURL(this.file)
        const link = document.createElement('a')

        link.target = '_blank'
        link.href = data
        link.download = this.file.name

        link.click()
    }

    /**
     * @param {Object} data
     */
     preview(data) {
        const content = this.modalContent.querySelector('.js-content')

        this.file = new File([data.file], data.filename, {
            type: data.file.type,
        })
        this.documentExtension = data.headers.get('document-extension')

        content.childNodes.forEach(elt => elt.remove())

        this.btnEditElt.dataset.path = this.manager.containerElt.dataset.pathShow.replace('__id__', data.headers.get('document-id'))
        this.btnDownloadElt.dataset.path  = this.manager.containerElt.dataset.pathDownload.replace('__id__', data.headers.get('document-id'))

        if (this.file.type === 'application/pdf') {
            content.append(this.#createObject())
        } else if(['image/png', 'image/jpeg', 'image/jpg'].includes(this.file.type)) {
            content.append(this.#createImg())
        } else {
            content.append(this.#createParagraph())
        }

        this.previewModal.show()
    }

    #createObject() {
        this.modalContent.classList.add('h-100')
        this.modalContent.classList.remove('align-items-center')

        const textPdfEmbed = document.createElement('p').innerText = 'Votre navigateur ne peux pas lire ce document.';
        const embedElt = document.createElement('embed')
        const objectElt = document.createElement('object')
        const objectUrl = window.URL.createObjectURL(this.file)


        embedElt.src = objectUrl
        embedElt.type = this.file.type
        embedElt.append(textPdfEmbed)

        objectElt.append(embedElt)
        objectElt.data = objectUrl
        objectElt.title = this.file.name
        objectElt.type = this.file.type
        objectElt.height = 'auto'
        objectElt.width = '100%'
        objectElt.classList.add('h-100')

        return objectElt
    }

    #createImg() {
        this.modalContent.classList.remove('h-100')
        this.modalContent.classList.add('align-items-center')
        
        const imgElt = new Image() 

        imgElt.src = window.URL.createObjectURL(this.file)
        imgElt.alt = this.file.name

        imgElt.onload = function() {
            if (imgElt.height > imgElt.width) {
                imgElt.style.height = '100vh'
            } else {
                imgElt.style.width = '100vw'
            }
        }

        return imgElt
    }

    #createParagraph() {
        this.modalContent.classList.remove('h-100')
        this.modalContent.classList.add('align-items-center')

        const pElt = document.createElement('p')

        pElt.textContent = 'Ce document ne peut pas être pré-visualiser.'
        pElt.classList.add('text-white', 'my-5')
        pElt.style.height = '250px'

        return  pElt
    }
}
