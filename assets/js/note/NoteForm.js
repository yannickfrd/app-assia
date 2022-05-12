import NoteManager from './NoteManager'
import MessageFlash from '../utils/messageFlash'
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
        this.noteModalElt = noteManager.noteModalElt
        this.noteModal = noteManager.noteModal
        this.supportId = noteManager.supportId
        this.confirmModal = noteManager.confirmModal
        this.confirmModalElt = noteManager.confirmModalElt
        this.responseAjax = this.noteManager.responseAjax.bind(this.noteManager)

        this.parametersUrl = new ParametersUrl()
        this.ckEditor = new CkEditor('#editor')

        this.formNoteElt = this.noteModalElt.querySelector('form[name=note]')
        this.contentElt = this.noteModalElt.querySelector('#note_content')
        this.btnExportWordElt = this.noteModalElt.querySelector('#export-note-word')
        this.btnExportPdfElt = this.noteModalElt.querySelector('#export-note-pdf')
        this.btnDeleteElt = this.noteModalElt.querySelector('#modal-btn-delete')
        
        this.autoSaveElt = document.getElementById('js-auto-save')

        this.tagsManager = new TagsManager()
        this.tagsSelectManager = new SelectManager('#note_tags', {name: 'onModal', elementId: this.noteModalElt.id})

        this.init()
        this.autoSaver = new AutoSaver('#editor', this.autoSave.bind(this), 60, 20)
    }

    init() {
        this.noteModalElt.querySelector('button[data-action="save"]')
            .addEventListener('click', e => {
                e.preventDefault()
                this.autoSaver.clear()
                this.requestToSave()
            })
        this.noteModalElt.querySelector('button[data-action="close"]')
            .addEventListener('click', e => {
                e.preventDefault()
                this.tryCloseModal()
                this.autoSaver.clear()
            })
        this.btnDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.requestToDelete()
        })
        this.noteModalElt.addEventListener('mousedown', e => {
            if (e.target === this.noteModalElt) {
                this.tryCloseModal(e)
            }
        })
        this.confirmModalElt.querySelector('#modal-confirm-btn')
            .addEventListener('click', () => this.onclickModalConfirmBtn())
    }

    resetForm() {
        this.noteModal.show()

        this.noteModalElt.querySelector('form').action = `/support/${this.supportId}/note/new`
        this.noteModalElt.querySelector('#note_title').value = ''
        this.contentElt.textContent = ''
        this.noteModalElt.querySelector('#note_type').value = 1
        this.noteModalElt.querySelector('#note_status').value = 1

        this.ckEditor.setData('')

        this.btnDeleteElt.classList.add('d-none')
        this.btnExportWordElt.classList.add('d-none')
        this.btnExportPdfElt.classList.add('d-none')

        this.tagsSelectManager.clearSelect()

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
        this.tagsSelectManager.updateSelect(tagsIds)

        this.noteModal.show()

        this.loader.off()
    }

    /**
     * @param {Object} note
     */
    initModal(note) {
        this.noteModalElt.querySelector('form').action = `/note/${note.id}/edit`

        this.btnDeleteElt.classList.remove('d-none')
        this.btnDeleteElt.dataset.pathDelete = `/note/${note.id}/delete`

        this.btnExportWordElt.classList.remove('d-none')
        this.btnExportWordElt.href = `/note/${note.id}/export/word`
        this.btnExportPdfElt.classList.remove('d-none')
        this.btnExportPdfElt.href = `/note/${note.id}/export/pdf`

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
            return new MessageFlash('danger', 'Veuillez rédiger la note avant d\'enregistrer.')
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

    onclickModalConfirmBtn() {
        switch (this.confirmModalElt.dataset.action) {
            case 'delete_note':
                this.loader.on()
                this.ajax.send('GET', this.btnDeleteElt.dataset.pathDelete, this.responseAjax)
                break
            case 'hide_note_modal':
                this.noteModal.hide()
                break
        }
        this.confirmModalElt.dataset.action = ''
    }

    /**
     * Envoie la requête ajax pour supprimer la note.
     */
    requestToDelete() {
        if (this.loader.isActive()) {
            return
        }

        this.autoSaver.clear()

        const modalBody = this.confirmModalElt.querySelector('.modal-body')
        modalBody.innerHTML = "<p>Voulez-vous vraiment supprimer cette note ?</p>"
        this.confirmModalElt.dataset.action = 'delete_note'
        this.confirmModal.show()
    }

    /**
     * Vérifie si des modifications ont été apportées avant la fermeture de la modal.
     */
    tryCloseModal() {
        if (this.ckEditor.getData() === this.contentElt.textContent) {
            return this.noteModal.hide()
        }

        const modalBody = this.confirmModalElt.querySelector('.modal-body')
        modalBody.innerHTML = "<p>Attention, vous n'avez pas enregistrer les modifications. <br/>Continuez sans sauvegarder ?</p>"
        this.confirmModalElt.dataset.action = 'hide_note_modal'
        this.confirmModal.show()
    }
}
