import FileChecker from './fileChecker'
import MessageFlash from '../messageFlash'

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
        this.filesDocumentInput.addEventListener('input', e => this.drop(e))
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
            this.checkFiles(e.dataTransfer.files)
        }

        if (this.filesDocumentInput.files) {
            this.checkFiles(this.filesDocumentInput.files)
            this.filesDocumentInput.value = null
        }
    }

    /**
     * @param {FileList} files
     */
    checkFiles(files) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i]

            if (this.filesInCollection(file)) {
                return new MessageFlash('danger', 'Le fichier "' + file.name + '" a déjà été ajouté.')
            }

            this.addFile(file, this.fileChecker.isValid(file))
        }
    }

    /**
     * @param {File} file 
     * @param {Boolean} isValid 
     */
    addFile(file, isValid) {
        if (0 === this.filesCollection.length) {
            this.createDropzoneContent()
        }

        const filename = this.fileChecker.getFilename(file)
        
        this.addItemInCollection(filename)
        this.updateDropzoneContent(filename)

        if (!isValid) {
            return this.updateItemInList(file, 'danger')
        }
        
        this.uploadCallback(file)
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
        <div class='row' style='min-height: 200px;'>
            <div class="col-md-12">
                <p class="mb-2"></p>
                <ul class="mb-0 pl-4"></ul>
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
        liElt.className = 'mb-2 fade-in'
        liElt.setAttribute('data-file-name', filename.toLowerCase())
        liElt.innerHTML = `${filename}<span class="fas fa-sync-alt ml-2 text-secondary"></span>`
        return liElt
    }

    clearDropzoneContent() {
        this.dropzoneElt.innerHTML = this.dropzoneElt.getAttribute('data-placeholder')
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
                const classname = (status === 'success' ? 'far fa-check-circle' : 'fas fa-exclamation-triangle') + ' ml-2'
                if (liElt) {
                    liElt.classList.add('text-' + status)
                    liElt.querySelector('span.fas.fa-sync-alt').className = classname
                }
                fileItem.status = status
            }
        })
        return this.filesInProgress()
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