import NoteManager from './NoteManager'
import AlertMessage from '../utils/AlertMessage'
import AutoSaver from '../utils/form/autoSaver'
import ParametersUrl from '../utils/parametersUrl'
import CkEditor from '../utils/ckEditor'
import TagsManager from '../tag/TagsManager'
import SelectManager from '../utils/form/SelectManager'

export default class NoteForm {
    /**
     * @param {NoteManager} noteManager
     */
    constructor(noteManager) {
        this.noteManager = noteManager
        this.loader = noteManager.loader
        this.ajax = noteManager.ajax
        this.noteModalElt = noteManager.modalElt
        this.noteModal = noteManager.objectModal
        this.responseAjax = this.noteManager.responseAjax.bind(this.noteManager)

        this.parametersUrl = new ParametersUrl()
        this.ckEditor = new CkEditor('#editor')

        this.formNoteElt = this.noteModalElt.querySelector('form[name=note]')
        this.contentElt = this.noteModalElt.querySelector('#note_content')
        this.btnExportWordElt = this.noteModalElt.querySelector('[data-action="export_word"]')
        this.btnExportPdfElt = this.noteModalElt.querySelector('[data-action="export_pdf"]')
        this.btnDeleteElt = this.noteModalElt.querySelector('[data-action="delete"]')
        
        this.autoSaveElt = document.getElementById('js-auto-save')

        this.tagsManager = new TagsManager()
        this.tagsSelectManager = new SelectManager('#note_tags')

        this.init()
        this.autoSaver = new AutoSaver('#editor', this.autoSave.bind(this), 60, 20)
    }

    init() {
        this.noteModalElt.querySelector('button[data-action="save"]')
            .addEventListener('click', e => this.#requestSave(e))

        this.noteModalElt.querySelector('button[data-action="close"]')
            .addEventListener('click', e => this.#requestClose(e))

        this.btnDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.noteManager.showModalConfirm()
        })

        this.btnExportWordElt.addEventListener('click', e => {
            e.preventDefault()
            this.noteManager.requestExportWord()
        })

        this.btnExportPdfElt.addEventListener('click', e => {
            e.preventDefault()
            this.noteManager.requestExportPdf()
        })

        this.noteModalElt.addEventListener('mousedown', e => {
            if (e.target === this.noteModalElt) {
                this.tryCloseModal(e)
            }
        })
        // this.confirmModalElt.querySelector('#modal_confirm_btn')
        //     .addEventListener('click', () => this.noteModal.hide())
        //     this.ajax.send('GET', this.btnDeleteElt.dataset.pathDelete, this.responseAjax)
    }

    /**
     * @param {Event} e 
     */
    #requestSave(e) {
        e.preventDefault()
        this.autoSaver.clear()
        this.requestToSave()
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
        this.#resetForm()
    }

    #resetForm() {
        this.noteModalElt.querySelector('form').action = this.noteManager.pathCreate(this.noteManager.supportId)
        this.noteModalElt.querySelector('#note_title').value = ''
        this.contentElt.textContent = ''
        this.noteModalElt.querySelector('#note_type').value = 1
        this.noteModalElt.querySelector('#note_status').value = 1

        this.ckEditor.setData('')

        this.btnDeleteElt.classList.add('d-none')
        this.btnExportWordElt.classList.add('d-none')
        this.btnExportPdfElt.classList.add('d-none')

        this.tagsSelectManager.clearItems()

        this.autoSaver.init()
    }

    /**
     * @param {Object} note
     */
    show(note) {
        this.initModal(note)

        this.noteModalElt.querySelector('#note_title').value = note.title ?? ''
        this.noteModalElt.querySelector('#note_type').value = note.type ?? ''
        this.noteModalElt.querySelector('#note_status').value = note.status ?? ''
        this.contentElt.textContent = note.content ?? ''
        this.ckEditor.setData(this.contentElt.textContent)

        const tagsIds = []
        note.tags.forEach(tags => tagsIds.push(tags.id))
        this.tagsSelectManager.updateItems(tagsIds)

        this.loader.off()
    }

    /**
     * @param {Object} note
     */
    initModal(note) {
        this.noteModalElt.querySelector('form').action = this.noteManager.pathEdit(note.id)

        this.btnDeleteElt.classList.remove('d-none')

        this.btnExportWordElt.classList.remove('d-none')
        this.btnExportPdfElt.classList.remove('d-none')

        this.autoSaver.init()
    }

    /**
     * Envoie la requête ajax pour sauvegarder la note.
     */
    requestToSave() {
        if (this.loader.isActive()) {
            return
        }

        if (this.ckEditor.getData() === '') {
            return new AlertMessage('danger', 'Veuillez rédiger la note avant d\'enregistrer.')
        }

        if (this.ckEditor.getData() !== this.contentElt.textContent) {
            this.contentElt.textContent = this.ckEditor.getData()
        }

        if (!this.autoSaver.active) {
            this.loader.on()
        }

        const url = this.formNoteElt.action
        this.ajax.send('POST', url, this.responseAjax, new FormData(this.formNoteElt))
    }

    autoSave() {
        this.autoSaveElt.classList.add('d-block')
        setTimeout(() => {
            this.autoSaveElt.classList.remove('d-block')
            this.autoSaver.clear()
        }, 4000)
        this.requestToSave()
    }

    /**
     * Vérifie si des modifications ont été apportées avant la fermeture de la modal.
     */
    tryCloseModal() {
        if (this.ckEditor.getData() === this.contentElt.textContent) {
            return this.noteModal.hide()
        }

        this.noteManager.confirmModal.show()
    }
}
