import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import ParametersUrl from '../utils/parametersUrl'
import {Modal} from 'bootstrap'
import NoteForm from './NoteForm'

export default class NoteManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax()
        this.parametersUrl = new ParametersUrl()

        this.noteModalElt = document.getElementById('note-modal')
        this.noteModal = new Modal(this.noteModalElt, {backdrop: 'static', keyboard: false})

        this.noteElts = document.querySelectorAll('div[data-note-id]')
        this.deleteNoteBtn = document.querySelectorAll('table#table-notes tbody button[data-action="delete_note"]')

        this.confirmModalElt = document.getElementById('confirm-modal')
        this.confirmModal = new Modal(this.confirmModalElt)

        this.deleteModalElt = document.getElementById('modal-block')
        this.deleteModal = new Modal(this.deleteModalElt)

        this.searchSupportNotesElt = document.getElementById('js-search-support-notes')
        this.themeColor = document.getElementById('header').dataset.color
        this.autoSaveElt = document.getElementById('js-auto-save')
        this.countNotesElt = document.getElementById('count-notes')
        this.containerNotesElt = document.getElementById('container-notes')

        this.supportId = this.containerNotesElt.dataset.support

        this.noteForm = new NoteForm(this)

        this.init()
    }

    init() {
        this.isCardNoteView = Boolean(document.querySelector('div.container[data-view="card-table"]').dataset.isCard)

        this.noteElts.forEach(noteElt => {
            if (!noteElt.dataset.noteDeleted) {
                noteElt.addEventListener('click', () => this.noteForm.show(noteElt))
            }
        })

        this.deleteNoteBtn.forEach(btn => btn.addEventListener('click', () => {
            this.deleteModal.show()
            this.deleteModalElt.querySelector('button#modal-confirm').dataset.url = btn.dataset.url
        }))

        document.querySelector('button[data-action="new_note"]').addEventListener('click', () => {
            this.noteForm.resetForm()
        })

        this.confirmModalElt.querySelector('#modal-confirm-btn').addEventListener('click', () => this.onclickModalConfirmBtn())

        this.deleteModalElt.querySelector('button#modal-confirm')
            .addEventListener('click', e => this.requestDeleteNote(e))

        this.checkIfNoteIdInUrl()
    }

    checkIfNoteIdInUrl() {
        const noteElt = this.containerNotesElt.querySelector(`div[data-note-id="${parseInt(this.parametersUrl.get('noteId'))}"]`)
        if (noteElt) {
            setTimeout(() => this.noteForm.show(noteElt), 200)
        }
    }

    onclickModalConfirmBtn() {
        switch (this.confirmModalElt.dataset.action) {
            case 'delete_note':
                this.loader.on()
                this.ajax.send('GET', this.noteForm.btnDeleteElt.dataset.url, this.responseAjax.bind(this))
                break;
            case 'hide_note_modal':
                this.noteModal.hide()
                break;
        }
        this.confirmModalElt.dataset.action = ''
    }

    /**
     * @param {Event} e
     */
    requestDeleteNote(e) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', e.target.dataset.url, this.responseAjax.bind(this))
        }
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
     * Réponse du serveur.
     * @param response
     */
    responseAjax(response) {
        if (!response.action) {
            return null
        }

        const note = response.note

        switch (response.action) {
            case 'create':
                this.createNoteElt(note)
                break
            case 'update':
                this.updateNoteElt(note)
                break
            case 'delete':
                this.deleteNoteElt(note)
                break
        }

        if (!this.noteForm.autoSaver.active && response.msg) {
            new MessageFlash(response.alert, response.msg)
            this.loader.off()
        }
    }

    /**
     * Crée la note dans le container.
     * @param {Object} note
     */
    createNoteElt(note) {
        const noteElt = document.createElement('div')
        noteElt.className = 'col-sm-12 col-lg-6 mb-4 reveal'
        noteElt.dataset.noteId = note.id

        noteElt.innerHTML = `
            <div class='card h-100 shadow'>
                <div class='card-header'>
                    <h3 class='card-title h5 text-${this.themeColor}'>${this.noteModalElt.querySelector('#note_title').value}</h3>
                    <span data-note-type="1">${note.typeToString}</span> (<span data-note-status="1">${note.statusToString}</span>)
                    <span class="small text-secondary" data-note-created="true">${this.getEditInfos(note)}</span>
                    <span class="small text-secondary" data-note-updated="true"></span>
                    <div class="mt-2 tags-list">${this.createTags(note)}</div>
                </div>
                <div class='card-body note-content cursor-pointer' data-placement='bottom' title='Voir la note'>
                    <div class='card-text'>${this.noteForm.ckEditor.getData()}</div>
                    <span class='note-fadeout'></span>
                </div>
            </div>`

        this.noteModalElt.querySelector('form').action = `/note/${note.id}/edit`

        this.containerNotesElt.firstChild.before(noteElt)
        // Met à jour le nombre de notes
        this.updateCounter(1)

        this.noteForm.initModal(noteElt)

        // Créé l'animation d'apparition
        setTimeout(() => noteElt.classList.add('reveal-on'), 100)

        noteElt.addEventListener('click', () => this.noteForm.show(noteElt))
    }

    /**
     * @param {Object} note
     * @returns {string}
     */
    getEditInfos(note) {
        return `(modifié le ${note.updatedAtToString} par ${note.updatedByToString})`
    }

    /**
     * @param {Object} note
     * @returns {string}
     */
    createTags(note) {
        return note.tags.reduce(
            (tags, tag) => tags + `<span class="badge bg-${tag.color} text-light mr-1" data-tag-id="${tag.id}">${tag.name}</span>`, ''
        )
    }

    /**
     * Met à jour la note dans le container.
     * @param {Object} note
     */
    updateNoteElt(note) {
        const noteElt = document.querySelector(`div[data-note-id="${note.id}"]`)

        noteElt.querySelector('.card-title').textContent = this.noteModalElt.querySelector('#note_title').value
        noteElt.querySelector('.card-text').innerHTML = this.noteForm.ckEditor.getData()

        const noteTypeElt = noteElt.querySelector('[data-note-type]')
        noteTypeElt.textContent = note.typeToString
        noteTypeElt.dataset.noteType = this.noteModalElt.querySelector('#note_type').value

        const noteStatusElt = noteElt.querySelector('[data-note-status]')
        noteStatusElt.textContent = note.statusToString
        noteStatusElt.dataset.noteStatus = this.noteModalElt.querySelector('#note_status').value

        noteElt.querySelector('[data-note-updated]').textContent = this.getEditInfos(note)
        noteElt.querySelector('.tags-list').innerHTML = this.createTags(note)
    }

    /**
     * @param {Object} note
     */
    deleteNoteElt(note) {
        if (this.isCardNoteView) {
            this.containerNotesElt.querySelector(`div[data-note-id="${note.id}"]`).remove()
            this.noteModal.hide()
        } else {
            const rowElt = document.getElementById('note-' + note.id)
            rowElt.remove()
        }
        this.updateCounter(-1)
    }

    /**
     * Met à jour le compteur du nombre de notes.
     * @param {Number} nb
     */
    updateCounter(nb) {
        const selector = this.isCardNoteView ? '.card' : 'table#table-notes tbody tr'
        const nbNotes = this.containerNotesElt.querySelectorAll(selector).length
        const nbTotalNotes = parseInt(this.countNotesElt.dataset.nbTotalNotes) + nb
        this.countNotesElt.dataset.nbTotalNotes = nbTotalNotes

        this.countNotesElt.textContent = `${nbNotes} note${nbNotes > 1 ? 's' : ''} sur ${nbTotalNotes}`

        if (parseInt(this.countNotesElt.textContent) > 0) {
            return this.searchSupportNotesElt.classList.remove('d-none')
        }
        return this.searchSupportNotesElt.classList.add('d-none')
    }
}
