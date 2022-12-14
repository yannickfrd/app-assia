import FileChecker from './fileChecker'
import AlertMessage from '../AlertMessage'
import { Tooltip } from 'bootstrap'

/**
 * Dropzone for files.
 */
export default class Dropzone {

    /**
     * 
     * @param {HTMLElement} formElt 
     * @param {CallableFunction} uploadCallback 
     */
    constructor(formElt, uploadCallback) {
        this.formElt = formElt
        this.uploadCallback = uploadCallback

        this.dropzoneElt = formElt.querySelector('#dropzone')
        this.filesDocumentInput = formElt.querySelector('#dropzone_document_files')

        this.fileChecker = new FileChecker()

        this.filesCollection = []
        this.content = ''

        this.init()
    }

    init() {
        this.clearDropzoneContent()

        this.dropzoneElt.addEventListener('click', () => this.filesDocumentInput.click())
        this.filesDocumentInput.addEventListener('input', () => this.inputFiles())
        this.dropzoneElt.addEventListener('dragenter', e => {
            e.stopPropagation()
            e.preventDefault()
            this.dropzoneElt.classList.add('dropzone-dragover')
        })
        this.dropzoneElt.addEventListener('dragleave', () => {
            this.dropzoneElt.classList.remove('dropzone-dragover')
        })
        this.dropzoneElt.addEventListener('dragover', e => {
            e.stopPropagation()
            e.preventDefault()
            this.dropzoneElt.classList.add('dropzone-dragover')
        })
        this.dropzoneElt.addEventListener('drop', e => this.drop(e))  
        document.addEventListener('drop', e => this.drop(e))
        this.formElt.querySelector('button[name="clear"]').addEventListener('click', e => {
            e.stopPropagation()
            e.preventDefault()
            this.clearDropzoneContent()
        })
    }

    /**
     * @param {Event} e 
     */
    drop(e) {
        e.stopPropagation()
        e.preventDefault()
        this.dropzoneElt.classList.remove('dropzone-dragover')
        
        if (e.dataTransfer) {
            const files = e.dataTransfer.files
            for (let i = 0; i < files.length; i++) {
                if (this.checkFile(files[i])) {
                    this.uploadCallback(files[i])
                }
            }         
        }
    }

    inputFiles() {       
        const files = this.filesDocumentInput.files
        let errors = 0
        for (let i = 0; i < files.length; i++) {
            if (!this.checkFile(files[i])) {
                ++errors
            }
        }

        if (0 === errors) {
            this.uploadCallback()
        }

        this.filesDocumentInput.value = null
    }

    /**
     * @param {File} file 
     * @returns {Boolean}
     */
    checkFile(file) {
        if (this.filesInCollection(file)) {
            new AlertMessage('danger', 'Le fichier "' + file.name + '" a d??j?? ??t?? ajout??.')
            return false
        }

        if (0 === this.filesCollection.length) {
            this.createDropzoneContent()
        }

        const filename = this.fileChecker.getFilename(file)
        
        this.addItemInCollection(filename)
        this.updateDropzoneContent(filename)

        if (!this.fileChecker.isValid(file)) {
            this.updateItemInList(file, 'danger')
            return false
        }

        return true
    }

    /**
     * @param {String} filename 
     */
    addItemInCollection(filename) {
        this.filesCollection.push({
            filename: filename.toLowerCase(),
            status: 'in_progress'
        })
    }
    
    /**
     * @param {String} filename 
     */
    updateDropzoneContent(filename) {
        this.dropzoneElt.querySelector('ul').appendChild(this.createLiElt(filename))
        this.updateCounter()
    }

    createDropzoneContent() {
        this.dropzoneElt.innerHTML = `
        <div class='row p-2' style='min-height: 200px;'>
            <div class="col-md-12">
                <p class="mb-2"></p>
                <ul class="list-group mb-0 ps-0"></ul>
            </div>
        </div>`
    }

    updateCounter() {
        const pElt = this.dropzoneElt.querySelector('p')
        const nbFiles = this.filesCollection.length
        pElt.innerHTML = `<b>${nbFiles}</b> fichier${nbFiles > 1 ? 's' : ''} :`
    }

    /**
     * @param {String} filename 
     * @returns {HTMLLIElement}
     */
    createLiElt(filename) {
        const liElt = document.createElement('li')
        liElt.className = 'list-group-item d-flex justify-content-between align-items-center list-group-item-light fade-in'
        liElt.dataset.fileName = filename.toLowerCase()
        liElt.innerHTML = `${filename}<span class="fas fa-sync-alt ms-2 text-secondary"></span>`
        return liElt
    }

    clearDropzoneContent() {
        this.dropzoneElt.innerHTML = this.dropzoneElt.dataset.placeholder
        this.filesCollection = []
        this.content = ''
    }
    
    /**
     * @param {Object} file 
     * @param {String} status 
     * @returns {Boolean}
     */
    updateItemInList(file, status = 'success') {
        const fullFilename = (file.name + (file.extension ? '.' + file.extension : '')).toLowerCase()
        this.filesCollection.forEach(fileItem => {
            if (fileItem.filename === fullFilename) {
                const liElt = this.dropzoneElt.querySelector(`[data-file-name="${fullFilename}"]`)
                const classname = (status === 'success' ? 'far fa-check-circle' : 'fas fa-exclamation-triangle') + ' ms-2'

                if (liElt) {
                    liElt.classList.replace('list-group-item-light', 'list-group-item-' + status)
                    liElt.title = this.getMessage(file, status)
                    liElt.dataset.bsPlacement = 'bottom'

                    const spanElt = liElt.querySelector('span.fas.fa-sync-alt')
                    if (spanElt) {
                        spanElt.className = classname
                    }
                    new Tooltip(liElt)
                }
                
                fileItem.status = status
            }
        })
        return this.filesInProgress()
    }

    /**
     * 
     * @param {Object} file 
     * @param {string} status 
     */
    getMessage(file, status) {
        let message = ''

        switch (status) {
            case 'success':
                message = 'Le fichier est enregistr??.'
                break
            case 'warning':
                message =  `Le fichier ?? ${file.name} ?? a d??j?? ??t?? ajout??.`
                break
            default:
                message = `Le fichier ?? ${file.name} ?? est invalide.`
                break
        }

        if (status !== 'success') {
            new AlertMessage(status, message)
        }

        return message;
    }

    /**
     * @returns {Boolean}
     */
    filesInProgress() {
        let result = false
        this.filesCollection.forEach(fileItem => {
            if (fileItem.status === 'in_progress') {
                result = true
            }
        })
        return result
    }

    /**
     * @param {File} file 
     * @returns {Boolean}
     */
    filesInCollection(file) {
        const filename = file.name.toLowerCase()
        let result = false
        this.filesCollection.forEach(fileItem => {
            if (fileItem.filename === filename) {
                result = true
            }
        })
        return result
    }
}