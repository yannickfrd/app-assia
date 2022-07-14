import Ajax from '../utils/ajax'
import AlertMessage from '../utils/AlertMessage'
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
        
        this.confirmModalElt = document.getElementById('confirm-modal')
        this.confirmModal = new Modal(this.confirmModalElt)

        this.deleteModalElt = document.getElementById('modal-block')
        this.deleteModal = new Modal(this.deleteModalElt)

        this.searchSupportNotesElt = document.getElementById('accordion_search')
        this.autoSaveElt = document.getElementById('js-auto-save')
        this.countNotesElt = document.getElementById('count-notes')
        this.containerNotesElt = document.getElementById('container-notes')

        this.supportId = this.containerNotesElt.dataset.support

        this.noteForm = new NoteForm(this)

        this.init()
    }

    init() {
        this.isCardNoteView = Boolean(document.querySelector('div.container[data-view="card-table"]').dataset.isCard)

        document.querySelectorAll('button[data-action="restore"]').forEach(btnRestoreElt => btnRestoreElt
            .addEventListener('click', () => this.requestRestoreNote(btnRestoreElt)))

        // table view
        document.querySelectorAll('table#table-notes tbody a[data-action="show"]')
            .forEach(showNoteBtn => showNoteBtn.addEventListener('click', e => {
                e.preventDefault()
                this.requestToShow(e.currentTarget.href)
            }))
        // card view
        document.querySelectorAll('div[data-note-id]').forEach(noteElt => {
            const pathShow = noteElt.dataset.pathShow
            if (pathShow) {
                noteElt.addEventListener('click', () => this.requestToShow(pathShow))
            }
        })

        document.querySelectorAll('table#table-notes tbody button[data-action="delete-note"]')
            .forEach(btnElt => btnElt.addEventListener('click', () => {
                this.deleteModal.show()
                this.deleteModalElt.querySelector('button#modal-confirm').dataset.pathDelete = btnElt.dataset.pathDelete
            }))

        if (document.querySelector('button[data-action="new_note"]')) {
            document.querySelector('button[data-action="new_note"]')
                .addEventListener('click', () => this.noteForm.resetForm())
        }

        this.confirmModalElt.querySelector('#modal-confirm-btn')
            .addEventListener('click', () => this.requestConfirmModal())

        this.deleteModalElt.querySelector('button#modal-confirm')
            .addEventListener('click', e => this.requestDeleteNote(e))

        this.checkIfNoteIdInUrl()
    }

    checkIfNoteIdInUrl() {
        const noteElt = this.containerNotesElt.querySelector(`div[data-note-id="${parseInt(this.parametersUrl.get('noteId'))}"]`)
        if (noteElt) {
            this.requestToShow(noteElt.dataset.pathShow, true)
        }
    }

    requestConfirmModal() {
        switch (this.confirmModalElt.dataset.action) {
            case 'delete-note':
                this.loader.on()
                this.ajax.send('GET', this.noteForm.btnDeleteElt.dataset.pathDelete, this.responseAjax.bind(this))
                break
            case 'hide_note_modal':
                this.noteModal.hide()
                break
        }
        this.confirmModalElt.dataset.action = ''
    }

    /**
     * @param {string} path
     * @param {boolean} force
     */
    requestToShow(path, force = false) {
        if (!this.loader.isActive() || force === true) {
            this.loader.on()

            this.ajax.send('GET', path, this.responseAjax.bind(this))
        }
    }

    /**
     * @param {HTMLLinkElement} btnRestoreElt
     */
    requestRestoreNote(btnRestoreElt) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', btnRestoreElt.dataset.url, this.responseAjax.bind(this))
        }
    }

    /**
     * @param {Event} e
     */
    requestDeleteNote(e) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', e.target.dataset.pathDelete, this.responseAjax.bind(this))
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
        this.confirmModalElt.dataset.action = 'delete-note'
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

        if (!this.noteForm.autoSaver.active && response.msg) {
            this.messageFlash = new AlertMessage(response.alert, response.msg)
            this.loader.off()
        }

        switch (response.action) {
            case 'create':
                this.createNoteElt(note)
                break
            case 'show':
                this.noteForm.show(note)
                break
            case 'update':
                this.updateNoteElt(note)
                break
            case 'delete':
                this.deleteNoteElt(note)
                break
            case 'restore':
                this.deleteNoteElt(note)
                this.checkToRedirect(this.messageFlash.delay)
                break
        }
    }

    /**
     * Crée la note dans le container.
     * @param {Object} note
     */
    createNoteElt(note) {
        if (this.isCardNoteView) {
            this.createCardNoteElt(note)
        } else {
            this.createTableRowNoteTr(note)
        }

        this.noteForm.initModal(note)

        this.updateCounter(1)
    }

    /**
     * Crée la note dans le container.
     * @param {Object} note
     */
    createCardNoteElt(note) {
        const noteElt = document.createElement('div')
        noteElt.className = 'col-sm-12 col-lg-6 mb-4 reveal'
        noteElt.dataset.noteId = note.id
        noteElt.dataset.pathShow = this.containerNotesElt.dataset.pathShow.replace('__id__', note.id)

        noteElt.innerHTML = `
            <div class='card h-100 shadow cursor-pointer'>
                <div class='card-header'>
                    <h3 class='card-title h5 text-primary'>${note.title}</h3>
                    <span data-note-type="${note.type}">${note.typeToString}</span> 
                    (<span data-note-status="${note.status}">${note.statusToString}</span>)
                    <span class="small text-secondary" data-note-created="true">Créé le ${note.createdAtToString}</span>
                    <span class="small text-secondary" data-note-updated="true"></span>
                    <div class="mt-2 tags-list">${this.createTags(note)}</div>
                </div>
                <div class='card-body note-content'>
                    <div class='card-text'>${this.noteForm.ckEditor.getData()}</div>
                    <span class='note-fadeout'></span>
                </div>
            </div>`

        this.noteModalElt.querySelector('form').action = `/note/${note.id}/edit`

        this.containerNotesElt.firstChild.before(noteElt)

        // Créé l'animation d'apparition
        setTimeout(() => noteElt.classList.add('reveal-on'), 100)

        noteElt.addEventListener('click', () => this.requestToShow(noteElt.dataset.pathShow))
    }

    /**
     * Crée la note dans le tableau.
     * @param {Object} note
     */
    createTableRowNoteTr(note) {
        const noteId = note.id
        const pathShow = this.containerNotesElt.dataset.pathShow.replace('__id__', noteId)
        const pathExportWord = this.containerNotesElt.dataset.pathExportWord.replace('__id__', noteId)
        const pathExportPdf = this.containerNotesElt.dataset.pathExportPdf.replace('__id__', noteId)
        const pathDelete = this.containerNotesElt.dataset.pathDelete.replace('__id__', noteId)
        const noteTr = document.createElement('tr')

        noteTr.id = 'note-' + noteId
        noteTr.innerHTML = `
            <td class="align-middle text-center">
                <a href="${pathShow.replace('__id__', noteId)}" type="button"
                    class="btn btn-primary btn-sm shadow" title="Voir la note sociale" 
                    data-bs-toggle="tooltip" data-bs-placement="bottom" data-action="show"><i class="fas fa-eye"></i>
                </a>
            </td>
            <td class="align-middle justify" data-cell="title-content">
                <span class="fw-bold">${note.title ? note.title : ''} : </span>${this.getNoteContent()}
            </td>
            <td class="align-middle" data-cell="type">${note.typeToString}</td>
            <td class="align-middle" data-cell="status">${note.statusToString}</td>
            <td class="align-middle" data-cell="tags">${this.createTags(note)}</td>
            <td class="align-middle" data-cell="createdAt">${note.createdAtToString}</td>
            <td class="align-middle text-center p-1">
                <a href="${pathExportWord}"
                    class="btn btn-primary btn-sm mb-1 shadow" title="Exporter la note au format Word"
                    data-bs-toggle="tooltip" data-bs-placement="bottom">
                        <i class="fas fa-file-word fa-lg bg-primary"></i><span class="visually-hidden">Word</span>
                </a>
                <a href="${pathExportPdf}"
                    class="btn btn-primary btn-sm mb-1 shadow" title="Exporter la note au format PDF"
                    data-bs-toggle="tooltip" data-bs-placement="bottom">
                        <i class="fas fa-file-pdf fa-lg bg-danger"></i><span class="visually-hidden">PDF</span>
                </a>
            </td>
            <td class="align-middle text-center">
                <button class="btn btn-sm btn-danger shadow" title="Supprimer la note" data-bs-toggle="tooltip" 
                    data-bs-placement="bottom" data-action="delete-note" data-path-delete="${pathDelete}">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `

        document.querySelector('table#table-notes tbody')
            .insertBefore(noteTr, document.querySelector('table#table-notes tbody').firstChild)

        document.querySelectorAll('table#table-notes tbody button[data-action="delete-note"]')
            .forEach(btnElt => btnElt.addEventListener('click', () => {
                this.deleteModal.show()
                this.deleteModalElt.querySelector('button#modal-confirm').dataset.pathDelete = btnElt.dataset.pathDelete
            }))

        noteTr.querySelector('a[data-action="show"]').addEventListener('click', e => {
            e.preventDefault()
            this.requestToShow(e.currentTarget.href)
        })
    }

    /**
     * @returns {string}
     */
    getNoteContent() {
        let content = this.noteForm.ckEditor.getData()
        content = content.replace(/(<([^>]+)>)/gi, ' ')

        return content.length >= 200 ? content.substr(0, 200) + ' [...]' : content
    }

    /**
     * @param {Object} note
     * @returns {string}
     */
     getUpdateInfos(note) {
        return `(modifié le ${note.updatedAtToString} par ${note.updatedByToString})`
    }

    /**
     * @param {Object} note
     * @returns {string}
     */
    createTags(note) {
        return note.tags.reduce(
            (tags, tag) => tags + `<span class="badge bg-${tag.color} me-1" data-tag-id="${tag.id}">${tag.name}</span>`, ''
        )
    }

    /**
     * Met à jour la note dans le container.
     * @param {Object} note
     */
    updateNoteElt(note) {
        if (this.isCardNoteView) {
            return this.updateCardNote(note)
        } 
        return this.updateRowNote(note)
    }

    /**
     * Met à jour la ligne dans le tableau.
     * @param {Object} note
     */
    updateRowNote(note) {
        const noteRow = document.querySelector('tr#note-' + note.id)

        noteRow.querySelector('td[data-cell="title-content"]').innerHTML = (note.title !== null
            ? `<span class="fw-bold">${note.title} : </span>` : ``) + `${this.getNoteContent()}`

        noteRow.querySelector('td[data-cell="type"]').innerHTML = note.typeToString ?? ''
        noteRow.querySelector('td[data-cell="status"]').innerHTML = note.statusToString ?? ''
        noteRow.querySelector('td[data-cell="tags"]').innerHTML = this.createTags(note)
        noteRow.querySelector('td[data-cell="createdAt"]').innerHTML = note.createdAtToString
    }

    /**
     * Met à jour la note dans le container.
     * @param {Object} note
     */
    updateCardNote(note) {
        const noteElt = document.querySelector(`div[data-note-id="${note.id}"]`)
        noteElt.querySelector('.card-title').textContent = this.noteModalElt.querySelector('#note_title').value
        noteElt.querySelector('.card-text').innerHTML = this.noteForm.ckEditor.getData()

        const noteTypeElt = noteElt.querySelector('[data-note-type]')
        noteTypeElt.textContent = note.typeToString
        noteTypeElt.dataset.noteType = this.noteModalElt.querySelector('#note_type').value

        const noteStatusElt = noteElt.querySelector('[data-note-status]')
        noteStatusElt.textContent = note.statusToString
        noteStatusElt.dataset.noteStatus = this.noteModalElt.querySelector('#note_status').value

        noteElt.querySelector('[data-note-updated]').textContent = this.getUpdateInfos(note)
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
        this.countNotesElt.dataset.nbTotalNotes = parseInt(this.countNotesElt.dataset.nbTotalNotes) + nb

        this.countNotesElt.textContent = `${nbNotes} note${nbNotes > 1 ? 's' : ''}`

        if (parseInt(this.countNotesElt.textContent) > 0) {
            return this.searchSupportNotesElt.classList.remove('d-none')
        }
        return this.searchSupportNotesElt.classList.add('d-none')
    }

    /**
     * Redirects if there are no more lines/card.
     * @param {number} delay
     */
    checkToRedirect(delay) {
        const selector = this.isCardNoteView
            ? document.querySelectorAll('div#container-notes .card')
            : document.querySelectorAll('table#table-notes tbody tr')

        if (selector.length === 0) {
            setTimeout(() => {
                document.location.href = location.pathname
            }, delay * 1000)    
        }
    }
}
