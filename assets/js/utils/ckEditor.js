import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document'
// import language from '@ckeditor/ckeditor5-build-decoupled-document/build/translations/fr.js'

export default class CKEditor {

    /**
     * @param {String} eltId 
     */
    constructor(eltId) {
        this.editorElt = document.querySelector(eltId)
        this.editor
        this.init()
    }

    init() {
        DecoupledEditor
            .create(document.querySelector('#editor'), {
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

    getEditorElt() {
        return this.editorElt
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