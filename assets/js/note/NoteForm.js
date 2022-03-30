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
        this.noteModalElt.querySelector('button[data-action="save"]').addEventListener('click', e => {
            e.preventDefault()
            this.autoSaver.clear()
            this.requestToSave()
        })
        this.noteModalElt.querySelector('button[data-action="close"]').addEventListener('click', e => {
            e.preventDefault()
            this.tryCloseModal()
            this.autoSaver.clear()
        })
        this.btnDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.requestToDelete()
        })
        this.confirmModalElt.querySelector('#modal-confirm-btn').addEventListener('click', () => this.onclickModalConfirmBtn())
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
     * @param {HTMLElement} noteElt
     */
    show(noteElt) {
        this.initModal(noteElt)

        this.noteModalElt.querySelector('#note_title').value = this.noteManager.isCardNoteView
            ? noteElt.querySelector('.card-title').textContent
            : noteElt.querySelector('td[data-cell="title-content"]').dataset.title ?? ''

        this.noteModalElt.querySelector('#note_type').value = this.noteManager.isCardNoteView
            ? noteElt.querySelector('[data-note-type]').dataset.noteType
            : noteElt.querySelector('td[data-cell="type"]').dataset.noteType

        this.noteModalElt.querySelector('#note_status').value = this.noteManager.isCardNoteView
            ? noteElt.querySelector('[data-note-status]').dataset.noteStatus
            : noteElt.querySelector('td[data-cell="status"]').dataset.noteStatus

        this.contentElt.textContent = this.noteManager.isCardNoteView
            ? noteElt.querySelector('.card-text').innerHTML
            : noteElt.querySelector('td[data-cell="title-content"]').dataset.content ?? ''
        this.ckEditor.setData(this.contentElt.textContent)

        this.updateTagsSelect(noteElt)

        this.noteModal.show()
    }

    /**
     * Donne la note sélectionnée dans le formulaire modal.
     * @param {HTMLElement} noteElt
     */
    initModal(noteElt) {
        const noteId = this.noteManager.isCardNoteView ? noteElt.dataset.noteId : noteElt.id.split('-')[1]
        this.noteModalElt.querySelector('form').action = `/note/${noteId}/edit`

        this.btnDeleteElt.classList.remove('d-none')
        this.btnDeleteElt.dataset.url = `/note/${noteId}/delete`

        this.btnExportWordElt.classList.remove('d-none')
        this.btnExportWordElt.href = `/note/${noteId}/export/word`
        this.btnExportPdfElt.classList.remove('d-none')
        this.btnExportPdfElt.href = `/note/${noteId}/export/pdf`

        this.autoSaver.init()
    }


    /**
     * Initialise les valeurs dans le multi-select
     * @param {Object} noteElt
     */
    updateTagsSelect(noteElt) {
        const tagElts = this.noteManager.isCardNoteView
            ? noteElt.querySelectorAll('.tags-list span')
            : noteElt.querySelectorAll('td[data-cell="tags"] span')

        const tagOptionElts = this.noteModalElt.querySelectorAll('option')
        const tagsIds = this.tagsManager.getTagIds(tagElts, tagOptionElts)

        this.tagsSelectManager.showOptionsFromArray(tagsIds)
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
        this.ajax.send('POST', url, this.noteManager.responseAjax.bind(this.noteManager), new FormData(this.formNoteElt))
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
                this.ajax.send('GET', this.btnDeleteElt.dataset.url, this.noteManager.responseAjax.bind(this.noteManager))
                break;
            case 'hide_note_modal':
                this.noteModal.hide()
                break;
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
