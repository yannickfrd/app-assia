import AbstractForm from '../utils/form/AbstractForm'
import NoteManager from './NoteManager'
import CkEditor from '../utils/ckEditor'
import AutoSaver from '../utils/form/autoSaver'
import AlertMessage from '../utils/AlertMessage'

export default class NoteForm extends AbstractForm 
{
    /**
     * @param {NoteManager} manager
     */
    constructor(manager) {
        super(manager)

        this.noteModalElt = manager.modalElt

        this.contentElt = this.formElt.querySelector('#note_content')
        this.btnExportWordElt = this.formElt.querySelector('[data-action="export_word"]')
        this.btnExportPdfElt = this.formElt.querySelector('[data-action="export_pdf"]')
        this.autoSaverElt = this.noteModalElt.querySelector('[data-auto-saver]')

        this.ckEditor = new CkEditor('#editor')
        this.autoSaver = new AutoSaver('#editor', e => this.autoSave(e), 60, 20)

        this.init()
    }

    init() {
        this.noteModalElt.querySelector('button[data-action="close"]')
            .addEventListener('click', e => this.#requestClose(e))

        this.btnExportWordElt.addEventListener('click', e => {
            e.preventDefault()
            this.manager.requestExportWord()
        })

        this.btnExportPdfElt.addEventListener('click', e => {
            e.preventDefault()
            this.manager.requestExportPdf()
        })
    }

    /**
     * @param {Event} e 
     */
    #requestClose(e) {
        e.preventDefault()
        this.tryCloseModal()
        this.autoSaver.clear()
    }

    new() {
        this.resetForm()

        this.formData = new FormData(this.formElt)

        this.formElt.querySelector('input').focus()

        this.ckEditor.setData('')

        this.hideButtons()

        this.autoSaver.init()
    }

    /**
     * @param {Object} note
     */
    show(note) {
        this.hydrateForm(note)

        this.formData = new FormData(this.formElt)
        
        this.focusFirstInput()

        this.ckEditor.setData(note.content)  

        this.displayButtons()

        this.autoSaver.init()
    }

    /**
     * @param {Object} note
     */
    afterCreate(note) {
        this.formElt.action = this.manager.pathEdit(note.id)

        this.displayButtons()
    }

    /**
     * Try to save the note by Ajax request.
     */
    requestToSave(e) {
        e.preventDefault()

        if (this.loader.isActive()) {
            return
        }

        this.autoSaver.clear()

        if (this.ckEditor.getData() === '') {
            return new AlertMessage('danger', 'Veuillez rédiger la note avant d\'enregistrer.')
        }

        this.contentElt.value = this.ckEditor.getData()

        this.formData = new FormData(this.formElt)

        this.ajax.send('POST', this.formElt.action, this.responseAjax, this.formData)
    }

    autoSave(e) {
        this.autoSaverElt.classList.remove('d-none')

        setTimeout(() => {
            this.autoSaverElt.classList.add('d-none')
            this.autoSaver.clear()
        }, 3000)

        this.requestToSave(e)
    }

    /**
     * Check if the form has modifications before to close modal.
     */
     tryCloseModal() {
        if (this.ckEditor.getData() === this.contentElt.value
            && false === this.formDataIsChanged() 
            || window.confirm(this.modalElt.dataset.confirmBeforeClose)
        ) {
            this.manager.objectModal.hide()
        }
    }

    /**
     * Display buttons to export to Word or PDF
     */
    displayButtons() {
        this.btnExportWordElt.classList.remove('d-none')
        this.btnExportPdfElt.classList.remove('d-none')
    }

    /**
     * Hide buttons to export to Word or PDF
     */
    hideButtons() {
        this.btnExportWordElt.classList.add('d-none')
        this.btnExportPdfElt.classList.add('d-none')
    }
}