import MessageFlash from '../messageFlash'

/**
 * Check if a file is valid or not (size, extension).
 */
export default class FileChecker {

    constructor(maxSize = 5, validExtensions = null) {
        this.validExtensions = validExtensions ? validExtensions : ['csv', 'doc', 'docx', 'jpg', 'jpeg', 'odp', 'ods', 'odt', 'pdf', 'png', 'rar', 'txt', 'xls', 'xlsx', 'zip']
        this.maxSize = maxSize
    }

    /**
     * @param {File} file
     * @returns {Boolean}
     */
    isValid(file) {
        const fileName = this.getFilename(file)
        const extensionFile = this.getExtension(file)
        const sizeFile = this.getSize(file)

        if (!this.isValidExtension(extensionFile)) {
            new MessageFlash('danger', `Le format du fichier "${fileName}" n'est pas valide (${extensionFile}).\n Formats acceptés : ${this.validExtensions.join(', ')}.`)
            return false
        }

        if (!this.isValidSize(sizeFile)) {
            new MessageFlash('danger', `Le fichier "${fileName}" est trop volumineux (${sizeFile} Mo). Maximum : ${this.maxSize} Mo.`)
            return false
        }

        return true
    }

    /**
     * @param {String} extensionFile 
     * @returns {Boolean}
     */
    isValidExtension(extensionFile) {
        return this.validExtensions.includes(extensionFile)
    }
    /**
     * @param {String} extensionFile 
     * @returns {Boolean}
     */
    isValidSize(sizeFile) {
        return sizeFile <= this.maxSize
    }

    /**
     * @param {File} file
     * @returns {String}
     */
    getFilename(file) {
        return file.name.split('\\').pop()
    }

    /**
     * @param {File} file
     * @returns {String}
     */
    getExtension(file) {
        return file.name.split('.').pop().toLowerCase()
    }

    /**
     * Get size of file in Mo.
     * @param {File} file
     * @return {Number}
     */
    getSize(file) {
        return Math.round((file.size / 1024 / 1024) * 10) / 10
    }
}