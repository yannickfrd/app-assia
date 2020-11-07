import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import SelectType from '../utils/selectType'
import { Modal } from 'bootstrap'
import Ajax from '../utils/ajax'

/**
 * Classe de gestion des documents.
 */
export default class SupportDocuments {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.modalElt = new Modal(document.getElementById('modal-document'))
        this.modalDeleteElt = new Modal(document.getElementById('modal-block'))
        this.selectType = new SelectType()

        this.modalDocumentElt = document.getElementById('modal-document')
        this.formDocumentElt = this.modalDocumentElt.querySelector('form[name=document]')
        this.documentNameInput = this.modalDocumentElt.querySelector('#document_name')
        this.documentTypeInput = this.modalDocumentElt.querySelector('#document_type')
        this.documentContentInput = this.modalDocumentElt.querySelector('#document_content')
        this.documentBlockFile = this.modalDocumentElt.querySelector('.js-document-block-file')
        this.documentFileInput = this.modalDocumentElt.querySelector('#document_file')
        this.documentFileLabelElt = this.modalDocumentElt.querySelector('.custom-file-label')
        this.filePlaceholder = this.documentBlockFile.getAttribute('data-placeholder')
        this.btnSaveElt = this.modalDocumentElt.querySelector('#js-btn-save')
        this.btnDeleteElt = this.modalDocumentElt.querySelector('#modal-btn-delete')
        this.modalConfirmElt = document.getElementById('modal-confirm')

        this.themeColor = document.getElementById('header').getAttribute('data-color')
        this.countDocumentsElt = document.getElementById('count-documents')
        this.supportId = document.getElementById('container-documents').getAttribute('data-support')

        this.init()
    }

    init() {
        document.getElementById('js-new-document').addEventListener('click', this.newDocument.bind(this))

        this.documentFileInput.addEventListener('input', this.checkFile.bind(this))

        document.querySelectorAll('.js-document').forEach(documentElt => {
            documentElt.addEventListener('click', e => this.getDocument(e, documentElt))
            const btnDeleteElt = documentElt.querySelector('button.js-delete')
            btnDeleteElt.addEventListener('click', () => {
                this.modalDeleteElt.show()
                this.documentId = Number(btnDeleteElt.parentElement.parentElement.id.replace('document-', ''))
                this.modalConfirmElt.setAttribute('data-url', btnDeleteElt.getAttribute('data-url'))
            })
        })

        this.btnSaveElt.addEventListener('click', e => this.saveDocument(e))

        document.getElementById('js-btn-cancel').addEventListener('click', e => e.preventDefault())

        this.btnDeleteElt.addEventListener('click', e => this.deleteDocument(e, this.btnDeleteElt.href))

        this.modalConfirmElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.modalConfirmElt.getAttribute('data-url'), this.responseAjax.bind(this))
        })
    }

    /**
     * Affiche un formulaire modal vierge.
     */
    newDocument() {
        this.modalDocumentElt.querySelector('form').action = '/support/' + this.supportId + '/document/new'
        this.documentNameInput.value = ''
        this.selectType.setOption(this.documentTypeInput, null)
        this.documentContentInput.value = ''
        this.documentBlockFile.classList.remove('d-none')
        this.documentFileInput.value = null
        this.documentFileLabelElt.textContent = this.filePlaceholder
        this.documentFileLabelElt.classList.remove('small')
        this.btnDeleteElt.classList.replace('d-block', 'd-none')
        this.btnSaveElt.setAttribute('data-action', 'new')
        this.btnSaveElt.textContent = 'Enregistrer'
        this.modalElt.show()
    }



    /**
     * Donne le document sélectionné dans le formulaire modal.
     * @param {Event} e 
     * @param {HTMLElement} documentElt 
     */
    getDocument(e, documentElt) {
        if (e.target.localName != 'td') {
            return null
        }

        this.documentElt = documentElt
        this.contentDocumentElt = documentElt.querySelector('.document-content')

        this.documentId = Number(documentElt.id.replace('document-', ''))
        this.modalDocumentElt.querySelector('form').action = '/document/' + this.documentId + '/edit'

        this.nameDocumentElt = documentElt.querySelector('.js-document-name')
        this.documentNameInput.value = this.nameDocumentElt.textContent

        const typeValue = documentElt.querySelector('.js-document-type').getAttribute('data-value')
        this.selectType.setOption(this.documentTypeInput, typeValue)

        this.contentDocumentElt = documentElt.querySelector('.js-document-content')
        this.documentContentInput.value = this.contentDocumentElt.textContent

        this.documentBlockFile.classList.add('d-none')

        this.btnDeleteElt.classList.replace('d-none', 'd-block')
        this.btnDeleteElt.href = '/document/' + this.documentId + '/delete'

        this.btnSaveElt.setAttribute('data-action', 'edit')
        this.btnSaveElt.textContent = 'Mettre à jour'
        this.modalElt.show()
    }

    /**
     * Vérifie le fichier choisi.
     */
    checkFile() {
        let error = false
        const validExtensions = ['csv', 'doc', 'docx', 'jpg', 'odp', 'ods', 'odt', 'pdf', 'png', 'rar', 'txt', 'xls', 'xlsx', 'zip']
        const extensionFile = this.documentFileInput.value.split('.').pop().toLowerCase()
        // Vérifie si l'extension du fichier est valide
        if ((validExtensions.indexOf(extensionFile) === -1)) {
            error = true
            new MessageFlash('danger', `Le format du fichier n'est pas valide (${extensionFile}).\n'Formats acceptés : ${validExtensions.join(', ')}.`)
        }

        const sizeFile = Math.round((this.documentFileInput.files[0].size / 1024 / 1024) * 10) / 10
        // Vérifie si le fichier est supérieur à 5 Mo
        if (sizeFile > 5) {
            error = true
            new MessageFlash('danger', `Le fichier est trop volumineux (${sizeFile} Mo). Maximum : 5 Mo.`)
        }
        // Si le fichier est valide, affiche le nom
        if (error === false) {
            const fileName = this.documentFileInput.value.split('\\').pop()
            this.documentFileLabelElt.textContent = fileName
            this.documentFileLabelElt.classList.add('small')
            if (!this.documentNameInput.value) {
                this.documentNameInput.value = fileName.split('.').slice(0, -1).join('.')
            }
            // Sinon, retire le fichier de l'input
        } else {
            this.documentFileInput.value = null
            this.documentFileLabelElt.textContent = this.filePlaceholder
            this.documentFileLabelElt.classList.remove('small')
        }
    }

    /**
     * Enregistre le document.
     * @param {Event} e
     */
    saveDocument(e) {
        e.preventDefault()

        if (!this.documentNameInput.value) {
            return  new MessageFlash('danger', 'Le nom du document est vide.')
        }

        if (!this.selectType.getOption((this.documentTypeInput))) {
            return new MessageFlash('danger', 'Le type de document n\'est pas renseigné.')
        }

        if (this.btnSaveElt.getAttribute('data-action') === 'new' && !this.documentFileInput.value) {
            return new MessageFlash('danger', 'Il n\'y a pas de fichier choisi.')
        }
        
        this.loader.on()

        this.uploadFiles(this.formDocumentElt.getAttribute('action'))
    }

    /**
     * 
     * @param {String} url 
     */
    uploadFiles(url) {
        const formData = new FormData(this.formDocumentElt)
        formData.append('file', this.documentFileInput.files)
        
        this.ajax.send('POST', url, this.responseAjax.bind(this), formData)
    }

    /**
     * Envoie une requête ajax pour supprimer le document.
     * @param {Event} e
     * @param {String} url
     */
    deleteDocument(e, url) {
        e.preventDefault()
        this.loader.on()
        if (window.confirm('Voulez-vous vraiment supprimer ce document ?')) {
            this.ajax.send('GET', url, this.responseAjax.bind(this))
        }
    }

    /**
     * Réponse du serveur.
     * @param {Object} response 
     */
    responseAjax(response) {     
        if (response.code === 200) {
            switch (response.action) {
                case 'create':
                    this.createDocument(response.data)
                    break
                case 'update':
                    this.updateDocument(response.data)
                    break
                case 'delete':
                    document.getElementById('document-' + this.documentId).remove()
                    this.countDocumentsElt.textContent = parseInt(this.countDocumentsElt.textContent) - 1
                    break
                }
            }
            this.modalElt.hide()
            new MessageFlash(response.alert, response.msg)
            this.loader.off()
    }

    /**
     * Crée la ligne du nouveau document dans le tableau.
     * @param {Object} data 
     */
    createDocument(data) {
        const documentElt = document.createElement('tr')
        documentElt.id = 'document-' + data.documentId
        documentElt.className = 'js-document'

        documentElt.innerHTML = this.getPrototypeDocument(data)

        const containerDocumentsElt = document.getElementById('container-documents')
        containerDocumentsElt.insertBefore(documentElt, containerDocumentsElt.firstChild)
        this.countDocumentsElt.textContent = parseInt(this.countDocumentsElt.textContent) + 1
        documentElt.addEventListener('click', e => this.getDocument(e, documentElt))
        const btnDeleteElt = documentElt.querySelector('button.js-delete')
        btnDeleteElt.addEventListener('click', () => {
            this.modalDeleteElt.show()
            this.documentId = Number(btnDeleteElt.parentElement.parentElement.id.replace('document-', ''))
            this.modalConfirmElt.setAttribute('data-url', btnDeleteElt.getAttribute('data-url'))
        })
    }

    /**
     * Met à jour la ligne du tableau correspondant au document.
     * @param {Object} data 
     */
    updateDocument(data) {
        this.nameDocumentElt.textContent = this.documentNameInput.value
        const documentTypeInput = this.documentElt.querySelector('.js-document-type')
        documentTypeInput.textContent = data.type
        documentTypeInput.setAttribute('data-value', this.selectType.getOption(this.documentTypeInput))
        this.documentElt.querySelector('.js-document-content').textContent = this.documentContentInput.value
    }

    getPrototypeDocument(data) {
        const size = Math.floor(data.size / 1000) + ' Ko'

        return `<td scope='row' class='align-middle text-center'>
                    <a href='/document/${data.documentId}/read' class='btn btn-${this.themeColor} btn-sm shadow my-1' 
                        title='Télécharger le document'><span class='fas fa-file-download'></span>
                    </a>
                </rd>
                    <td class='align-middle js-document-name'>${this.documentNameInput.value}</td>
                    <td class='align-middle js-document-type'
                        data-value='${this.selectType.getOption(this.documentTypeInput)}'>${data.type}</td>
                    <td class='align-middle js-document-content'>${this.documentContentInput.value}</td>
                    <td class='align-middle js-document-size text-right'>${size}</td>
                    <td class='align-middle js-document-createdAt'>${data.createdAt}</td>
                    <td class='align-middle text-center'>
                        <button data-url='/document/${data.documentId}/delete' class='js-delete btn btn-danger btn-sm shadow my-1' 
                            title='Supprimer le document'><span class='fas fa-trash-alt'></span>
                        </button>
                </td>`
    }
}