
export default class DocumentForm {

    /**
     *  @param {DocumentManager} documentManager
     */
    constructor(documentManager) {
        this.documentManager = documentManager

        this.loader = documentManager.loader
        this.ajax = documentManager.ajax
        this.themeColor = documentManager.themeColor

        this.init()
    }

    init() {
    }

    /**
     * Use of the term "file" instead of "document" by conflicts with the DOM.
     * @param {Object} file
     */
    showDocument(file) {

    }
}