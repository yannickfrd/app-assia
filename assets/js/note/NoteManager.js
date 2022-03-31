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
        this.noteModal = new Modal(this.noteModalElt)

        this.noteElts = document.querySelectorAll('div[data-note-id]')

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

        document.querySelectorAll('table#table-notes tbody a[data-action="show"]')
            .forEach(showNoteBtn => showNoteBtn.addEventListener('click', e => {
                e.preventDefault()
                this.noteForm.show(e.currentTarget.parentElement.parentElement)
            }))

        this.noteElts.forEach(noteElt => {
            if (!noteElt.dataset.noteDeleted) {
                noteElt.addEventListener('click', () => this.noteForm.show(noteElt))
            }
        })

        document.querySelectorAll('table#table-notes tbody button[data-action="delete_note"]')
            .forEach(btn => btn.addEventListener('click', () => {
                this.deleteModal.show()
                this.deleteModalElt.querySelector('button#modal-confirm').dataset.url = btn.dataset.url
            }))

        document.querySelector('button[data-action="new_note"]')
            .addEventListener('click', () => this.noteForm.resetForm())

        this.confirmModalElt.querySelector('#modal-confirm-btn')
            .addEventListener('click', () => this.onclickModalConfirmBtn())

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
        if (this.isCardNoteView) {
            this.createCardNoteElt(note);
        } else {
            this.createTableRowNoteTr(note)
        }
        this.updateCounter(1)

        this.noteModal.hide()
    }

    /**
     * Crée la note dans le container.
     * @param {Object} note
     */
    createCardNoteElt(note) {
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

        this.noteForm.initModal(noteElt)

        // Créé l'animation d'apparition
        setTimeout(() => noteElt.classList.add('reveal-on'), 100)

        noteElt.addEventListener('click', () => this.noteForm.show(noteElt))
    }

    /**
     * Crée la note dans le tableau.
     * @param {Object} note
     */
    createTableRowNoteTr(note) {
        const noteId = note.id

        const showUrl = document.querySelector('table#table-notes thead th[data-get-url="show"]').dataset.showUrl
        const thAction = document.querySelector('table#table-notes thead th[data-get-url="action"]')

        const wordUrl = thAction.dataset.wordUrl.replace('__id__', noteId)
        const pdfUrl = thAction.dataset.pdfUrl.replace('__id__', noteId)
        const deleteUrl = thAction.dataset.deleteUrl.replace('__id__', noteId)

        const noteTr = document.createElement('tr')
        noteTr.id = 'note-' + noteId

        noteTr.innerHTML = `
            <td class="align-middle text-center">
                <a href="${showUrl.replace('__id__', noteId)}"
                    class="btn btn-${this.themeColor} btn-sm shadow" title="Voir la note sociale" type="button"
                    data-toggle="tooltip" data-placement="bottom"><i class="fas fa-eye"></i></a></td>`

        const content = note.content.length >= 200 ? note.content.substr(0, 200) + ' [...]' : note.content
        let titleTd = `<td class="align-middle justify" data-cell="title-content" data-title="${note.title}" data-content="${note.content}">`
        titleTd += note.title !== null ? `<span class="font-weight-bold">${note.title} : </span>` : ``
        titleTd += `${this.striptags(content)}</td>`
        noteTr.innerHTML += titleTd

        noteTr.innerHTML += `
            <td class="align-middle" data-cell="type">${note.typeToString}</td>
            <td class="align-middle" data-cell="status">${note.statusToString}</td>
            <td class="align-middle" data-cell="tags">${this.createTags(note)}</td>
            <td class="align-middle" data-cell="createdAt">${note.createdAtToString}</td>
            <td class="align-middle text-center p-1">
                <a href="${wordUrl}" class="btn btn-${this.themeColor} btn-sm mb-1 shadow" 
                    title="Exporter la note au format Word" data-toggle="tooltip" data-placement="bottom">
                    <i class="fas fa-file-word bg-primary fa-lg"></i><span class="sr-only">Word</span></a><br/>
                <a href="${pdfUrl}" class="btn btn-${this.themeColor} btn-sm mb-1 shadow" 
                    title="Exporter la note au format PDF" data-toggle="tooltip" data-placement="bottom">
                    <i class="fas fa-file-pdf bg-danger fa-lg"></i><span class="sr-only">PDF</span></a><br/>
                <button class="btn btn-sm btn-danger shadow mt-3" title="Supprimer la note" data-toggle="tooltip" data-placement="bottom"
                    data-action="delete_note" data-url="${deleteUrl}"><i class="fa-solid fa-trash-can"></i></button></td>`

        document.querySelector('table#table-notes tbody')
            .insertBefore(noteTr, document.querySelector('table#table-notes tbody').firstChild)

        this.noteModal.hide()

        document.querySelectorAll('table#table-notes tbody button[data-action="delete_note"]')
            .forEach(btn => btn.addEventListener('click', () => {
                this.deleteModal.show()
                this.deleteModalElt.querySelector('button#modal-confirm').dataset.url = btn.dataset.url
            }))
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
        if (this.isCardNoteView) {
            this.updateCardNote(note)
        } else {
            this.updateRowNote(note)
        }
        this.noteModal.hide()
    }

    /**
     * Met à jour la ligne dans le tableau.
     * @param {Object} note
     */
    updateRowNote(note) {
        const noteRow = document.querySelector('tr#note-' + note.id)

        const content = note.content.length >= 200 ? note.content.substr(0, 200) + ' [...]' : note.content

        noteRow.querySelector('td[data-cell="title-content"]').dataset.title = note.title
        noteRow.querySelector('td[data-cell="title-content"]').dataset.content = note.content
        noteRow.querySelector('td[data-cell="title-content"]').innerHTML = (note.title !== null
            ? `<span class="font-weight-bold">${note.title} : </span>` : ``) + `${this.striptags(content)}`

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

    striptags(text) {
        return text.replace(/(<([^>]+)>)/gi, ' ')
    }
}
