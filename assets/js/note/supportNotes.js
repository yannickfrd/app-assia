import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import AutoSaver from '../utils/form/autoSaver'
import ParametersUrl from '../utils/parametersUrl'
import { Modal } from 'bootstrap'
import CkEditor from '../utils/ckEditor'
import TagsManager from '../tag/TagsManager'
import SelectManager from '../utils/form/SelectManager'

export default class SupportNotes {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax()
        this.parametersUrl = new ParametersUrl()

        this.CkEditor = new CkEditor('#editor')

        this.newNoteBtnElt = document.getElementById('js-new-note')
        this.noteElts = document.querySelectorAll('div[data-note-id]')

        this.noteModal = new Modal(document.getElementById('note-modal'), {
            backdrop: 'static',
            keyboard: false,
        })
        this.noteModalElt = document.getElementById('note-modal')
        this.formNoteElt = this.noteModalElt.querySelector('form[name=note]')
        this.noteContentElt = this.noteModalElt.querySelector('#note_content')
        this.saveBtnElt = this.noteModalElt.querySelector('button[data-action="save"]')
        this.closeBtnElt = this.noteModalElt.querySelector('button[data-action="close"]')
        this.exportWordBtnElt = this.noteModalElt.querySelector('#export-note-word')
        this.exportPdfBtnElt = this.noteModalElt.querySelector('#export-note-pdf')
        this.deleteBtnElt = this.noteModalElt.querySelector('#modal-btn-delete')

        this.confirmModal = new Modal(document.getElementById('confirm-modal'))
        this.confirmModalElt = document.getElementById('confirm-modal')

        this.searchSupportNotesElt = document.getElementById('js-search-support-notes')
        this.themeColor = document.getElementById('header').dataset.color
        this.autoSaveElt = document.getElementById('js-auto-save')
        this.countNotesElt = document.getElementById('count-notes')
        this.containerNotesElt = document.getElementById('container-notes')
        this.supportId = this.containerNotesElt.dataset.support
        this.data = null
        
        this.tagsManager = new TagsManager()
        this.SelectManager = new SelectManager('#note_tags', {name: 'onModal', elementId: this.noteModalElt.id})

        this.init()
        this.autoSaver = new AutoSaver(this.autoSaveNote.bind(this), this.CkEditor.getEditorElt(), 60, 20)
    }

    init() {
        this.noteElts.forEach(noteElt => {
            noteElt.addEventListener('click', () => this.showNote(noteElt))
        })
        this.newNoteBtnElt.addEventListener('click', () => this.showNewNote())
        this.saveBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.autoSaver.clear()
            this.requestToSave()
        })
        this.closeBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.tryCloseModal()
            this.autoSaver.clear()
        })
        this.deleteBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.requestToDelete()
        })
        this.noteModalElt.addEventListener('mousedown', e => {
            if (e.target === this.noteModalElt) {
                this.tryCloseModal(e)
            }
        })
        this.confirmModalElt.querySelector('#modal-confirm-btn').addEventListener('click', () => this.onclickModalConfirmBtn())

        this.checkIfNoteIdInUrl()
    }

    checkIfNoteIdInUrl() {
        const noteElt = this.containerNotesElt.querySelector(`div[data-note-id="${parseInt(this.parametersUrl.get('noteId'))}"]`)
        if (noteElt) {
            setTimeout(() => {
                this.noteModal.show()
                this.showNote(noteElt)
            }, 200)
        }
    }

    /**
     * Affiche un formulaire modal vierge.
     */
    showNewNote() {
        this.SelectManager.clearSelect()
        this.noteModal.show()

        this.noteModalElt.querySelector('form').action = '/support/' + this.supportId + '/note/new'
        this.noteModalElt.querySelector('#note_title').value = ''
        this.noteModalElt.querySelector('#note_type').value = 1
        this.noteModalElt.querySelector('#note_status').value = 1

        this.CkEditor.setData('')
        this.data = ''
        this.deleteBtnElt.classList.replace('d-block', 'd-none')
        this.exportWordBtnElt.classList.replace('d-block', 'd-none')
        this.exportPdfBtnElt.classList.replace('d-block', 'd-none')
        this.autoSaver.init()

        const inputPlaceholder = document.querySelectorAll('input[placeholder=" -- Étiquettes --"]')
        inputPlaceholder.forEach(sel => sel.style.width = '100%')
    }


    /**
     * Donne la note sélectionnée dans le formulaire modal.
     * @param {HTMLElement} noteElt
     */
    showNote(noteElt) {
        this.initNoteModal(noteElt)

        this.noteModalElt.querySelector('#note_title').value = this.titleNoteElt.textContent
        this.noteModalElt.querySelector('#note_type').value = noteElt.querySelector('[data-note-type]').dataset.noteType
        this.noteModalElt.querySelector('#note_status').value = noteElt.querySelector('[data-note-status]').dataset.noteStatus

        const content  = this.contentNoteElt.innerHTML
        this.CkEditor.setData(content)
        this.noteContentElt.textContent = content
        this.data = this.CkEditor.getData()

        this.noteModal.show()

        this.updateTagsSelect(noteElt)
    }

    /**
     * Permet d'initialiser les valeurs dans le multi-select
     * @param {Object} noteElt
     */
    updateTagsSelect(noteElt){
        const tagElts = noteElt.querySelectorAll('.tags-list span')
        const tagOptionElts = this.noteModalElt.querySelectorAll('option')
        const tagsIds = this.tagsManager.getTagIds(tagElts, tagOptionElts)

        this.SelectManager.showOptionsFromArray(tagsIds)
    }

    /**
     * Donne la note sélectionnée dans le formulaire modal.
     * @param {HTMLElement} noteElt
     */
    initNoteModal(noteElt) {
        this.noteElt = noteElt
        this.contentNoteElt = noteElt.querySelector('.card-text')

        this.cardId = this.noteElt.dataset.noteId
        this.noteModalElt.querySelector('form').action = '/note/' + this.cardId + '/edit'

        this.titleNoteElt = noteElt.querySelector('.card-title')

        this.deleteBtnElt.classList.replace('d-none', 'd-block')
        this.deleteBtnElt.dataset.url = '/note/' + this.cardId + '/delete'

        this.exportWordBtnElt.classList.replace('d-none', 'd-block')
        this.exportWordBtnElt.href = '/note/' + this.cardId + '/export/word'
        this.exportPdfBtnElt.classList.replace('d-none', 'd-block')
        this.exportPdfBtnElt.href = '/note/' + this.cardId + '/export/pdf'

        this.autoSaver.init()
    }

    /**
     * Permet de créer les spans représentant les tags
     * @param {Object} note
     */
    updateTagsList(note){
        this.tagsManager.updateTagsContainer(this.noteElt.querySelector('.tags-list'), JSON.parse(note.tags))
    }

    /**
     * Envoie la requête ajax pour sauvegarder la note.
     */
    requestToSave() {
        if (true === this.loader.isActive()) {
            return
        }

        if (this.CkEditor.getData() === '') {
            return new MessageFlash('danger', 'Veuillez rédiger la note avant d\'enregistrer.')
        }

        if (this.CkEditor.getData() !== this.data) {
            this.noteContentElt.textContent = this.CkEditor.getData()
        }

        if (!this.autoSaver.active) {
            this.loader.on()
        }

        const url = this.formNoteElt.action
        this.ajax.send('POST', url, this.responseAjax.bind(this), new FormData(this.formNoteElt))
    }

      autoSaveNote() {
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
                this.ajax.send('GET', this.deleteBtnElt.dataset.url, this.responseAjax.bind(this))
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
        if (true === this.loader.isActive()) {
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
        if (this.CkEditor.getData() === this.data) {
            return this.noteModal.hide()
        }

        const modalBody = this.confirmModalElt.querySelector('.modal-body')
        modalBody.innerHTML = "<p>Attention, vous n'avez pas enregistrer les modifications. <br/>Continuez sans sauvegarder ?</p>"
        this.confirmModalElt.dataset.action = 'hide_note_modal'
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

        const note = response.data.note

        switch (response.action) {
            case 'create':
                this.createNote(note)
                break
            case 'update':
                this.updateNote(note)
                break
            case 'delete':
                this.deleteNote()
                break
        }

        if (!this.autoSaver.active && response.msg) {
            new MessageFlash(response.alert, response.msg)
            this.loader.off()
        }
    }

    /**
     * Crée la note dans le container.
     * @param {Object} note
     */
    createNote(note) {
        const responseTags = JSON.parse(note.tags)
        let listTags = ''

        if (responseTags.length > 0){
            responseTags.forEach(tag => {
                listTags += `<span class="badge bg-${tag.color} text-light mr-1" 
                            data-tag-id="${tag.id}">${tag.name}</span>`
            })
        }

        const noteElt = document.createElement('div')
        noteElt.className = 'col-sm-12 col-lg-6 mb-4 reveal'
        noteElt.dataset.noteId = note.id
        noteElt.innerHTML =
            `<div class='card h-100 shadow'>
                <div class='card-header'>
                    <h3 class='card-title h5 text-${this.themeColor}'>${this.noteModalElt.querySelector('#note_title').value}</h3>
                    <span data-note-type="1">${note.typeToString}</span> (<span data-note-status="1">${note.statusToString}</span>)
                    <span class="small text-secondary" data-note-created="true">${note.editionToString}</span>
                    <span class="small text-secondary" data-note-updated="true"></span>
                    <div class="mt-2 tags-list">${listTags}</div>
                </div>
                <div class='card-body note-content cursor-pointer' data-placement='bottom' title='Voir la note'>
                    <div class='card-text'>${this.CkEditor.getData()}</div>
                    <span class='note-fadeout'></span>
                </div>
            </div>`

        this.noteModalElt.querySelector('form').action = '/note/' + note.id + '/edit'
        this.deleteBtnElt.classList.replace('d-none', 'd-block')

        this.containerNotesElt.firstChild.before(noteElt)
        // Met à jour le nombre de notes
        this.updateCountNotes(1)

        this.initNoteModal(noteElt)
        this.data = this.CkEditor.getData()

        // Créé l'animation d'apparition
        setTimeout(() => {
            noteElt.classList.add('reveal-on')
        }, 100)

        noteElt.addEventListener('click', () => this.showNote(noteElt))
    }

    /**
     * Met à jour la note dans le container.
     * @param {Object} note
     */
    updateNote(note) {
        this.titleNoteElt.textContent = this.noteModalElt.querySelector('#note_title').value
        this.contentNoteElt.innerHTML = this.CkEditor.getData()
        this.data = this.CkEditor.getData()

        const noteTypeElt = this.noteElt.querySelector('[data-note-type]')
        noteTypeElt.textContent = note.typeToString
        noteTypeElt.dataset.noteType = this.noteModalElt.querySelector('#note_type').value

        const noteStatusElt = this.noteElt.querySelector('[data-note-status]')
        noteStatusElt.textContent = note.statusToString
        noteStatusElt.dataset.noteStatus = this.noteModalElt.querySelector('#note_status').value

        this.noteElt.querySelector('[data-note-updated]').textContent = note.editionToString

        this.updateTagsList(note)
    }

    deleteNote() {
        this.containerNotesElt.querySelector(`div[data-note-id="${this.cardId}"]`).remove()
        this.updateCountNotes(-1)
        this.noteModal.hide()
    }

    /**
     * Met à jour le compteur du nombre de notes.
     * @param {Number} nb
     */
    updateCountNotes(nb) {
        const nbNotes = this.containerNotesElt.querySelectorAll('.card').length
        const nbTotalNotes = parseInt(this.countNotesElt.dataset.nbTotalNotes) + nb
        this.countNotesElt.dataset.nbTotalNotes = nbTotalNotes

        this.countNotesElt.textContent = `${nbNotes} note${nbNotes > 1 ? 's' : ''} sur ${nbTotalNotes}`

        if (parseInt(this.countNotesElt.textContent) > 0) {
            return this.searchSupportNotesElt.classList.remove('d-none')
        }
        return this.searchSupportNotesElt.classList.add('d-none')
    }
}
