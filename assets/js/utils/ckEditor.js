import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document'
// import language from '@ckeditor/ckeditor5-build-decoupled-document/build/translations/fr.js'

export default class CkEditor {

    /**
     * @param {string} selector 
     */
    constructor(selector) {
        this.editorElt = document.querySelector(selector)
        this.editor = null
        this.init()
    }

    init() {
        DecoupledEditor
            .create(this.editorElt, {
                toolbar: ['undo', 'redo', '|', 'fontFamily', 'fontSize', '|', 'bold', 'italic', 'underline', 'highlight', '|', 'heading', 'alignment', '|', 'bulletedList', 'numberedList', '|', 'link', 'blockQuote', '|', 'insertTable'],
                language: {
                    ui: 'fr',
                    content: 'fr'
                },
            })
            .then(editor => {
                this.editor = editor
                const toolbarContainer = document.querySelector('#toolbar-container')
                toolbarContainer.appendChild(editor.ui.view.toolbar.element)
            })
            .catch(error => {
                throw new Error(error)
            })
    }

    /**
     * @returns {Object}
     */
    getData() {
        return this.editor.getData()
    }

    /**
     * @param {Object} data 
     */
    setData(data) {
        this.editor.setData(data)
    }
}