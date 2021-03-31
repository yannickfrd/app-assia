import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import AutoSave from '../utils/autoSave'
import SelectType from '../utils/selectType'
import ParametersUrl from '../utils/parametersUrl'
import { Modal } from 'bootstrap'
import CkEditor from '../utils/ckEditor'

export default class SupportNotes {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.selectType = new SelectType()
        this.parametersUrl = new ParametersUrl()
        this.noteModalElt = new Modal(document.getElementById('note-modal'))
        this.CkEditor = new CkEditor('#editor')
        
        this.newNoteBtn = document.getElementById('js-new-note')
        this.noteElts = document.querySelectorAll('div[data-note-id]')

        this.modalNoteElt = document.getElementById('note-modal')
        this.formNoteElt = this.modalNoteElt.querySelector('form[name=note]')
        this.noteContentElt = this.modalNoteElt.querySelector('#note_content')
        this.btnSaveElt = this.modalNoteElt.querySelector('button[data-action="save"]')
        this.btnCancelElt = this.modalNoteElt.querySelector('button[data-action="cancel"]')
        this.btnExportWordElt = this.modalNoteElt.querySelector('#export-note-word')
        this.btnExportPdfElt = this.modalNoteElt.querySelector('#export-note-pdf')
        this.btnDeleteElt = this.modalNoteElt.querySelector('#modal-btn-delete')
        
        this.searchSupportNotesElt = document.getElementById('js-search-support-notes')
        this.themeColor = document.getElementById('header').getAttribute('data-color')
        this.autoSaveElt = document.getElementById('js-auto-save')
        this.countNotesElt = document.getElementById('count-notes')
        this.nbTotalNotesElt = document.getElementById('nb-total-notes')
        this.containerNotesElt = document.getElementById('container-notes')
        this.supportId = this.containerNotesElt.getAttribute('data-support')
        this.data = null

        this.init()
        this.autoSave = new AutoSave(this.autoSaveNote.bind(this), this.CkEditor.getEditorElt(), 2 * 60, 20)
    }

    init() {
        this.noteElts.forEach(noteElt => {
            noteElt.addEventListener('click', () => this.showNote(noteElt))
        })
        this.newNoteBtn.addEventListener('click', () => this.showNewNote())
        this.btnSaveElt.addEventListener('click', e => this.requestToSave(e))
        this.btnCancelElt.addEventListener('click', e => this.autoSave.clear(e))
        this.btnDeleteElt.addEventListener('click', e => this.requestToDelete(e))
        this.modalNoteElt.addEventListener('mousedown', e => this.goOutModal(e))
        this.checkIfNoteIdInUrl()
    }

    checkIfNoteIdInUrl() {
        const noteElt = this.containerNotesElt.querySelector(`div[data-note-id="${Number(this.parametersUrl.get('noteId'))}"]`)
        if (noteElt) {
            setTimeout(() => {
                this.noteModalElt.show()
                this.showNote(noteElt)
            }, 200)
        }
    }

    /**
     * Affiche un formulaire modal vierge.
     */
    showNewNote() {
        this.noteModalElt.show()

        this.modalNoteElt.querySelector('form').action = '/support/' + this.supportId + '/note/new'
        this.modalNoteElt.querySelector('#note_title').value = ''
        this.selectType.setOption(this.modalNoteElt.querySelector('#note_type'), 1)
        this.selectType.setOption(this.modalNoteElt.querySelector('#note_status'), 1)
        this.CkEditor.setData('')
        this.btnDeleteElt.classList.replace('d-block', 'd-none')
        this.btnExportWordElt.classList.replace('d-block', 'd-none')
        this.btnExportPdfElt.classList.replace('d-block', 'd-none')
        this.autoSave.init()
    }


    /**
     * Donne la note sélectionnée dans le formulaire modal.
     * @param {HTMLElement} noteElt 
     * @param {Boolean} newNote 
     */
    showNote(noteElt) {
        this.noteModalElt.show()

        this.noteElt = noteElt
        this.contentNoteElt = noteElt.querySelector('.card-text')

        this.cardId = this.noteElt.getAttribute('data-note-id')
        this.modalNoteElt.querySelector('form').action = '/note/' + this.cardId + '/edit'

        this.titleNoteElt = noteElt.querySelector('.card-title')
        this.modalNoteElt.querySelector('#note_title').value = this.titleNoteElt.textContent

        const typeValue = noteElt.querySelector('[data-note-type]').getAttribute('data-note-type')
        this.selectType.setOption(this.modalNoteElt.querySelector('#note_type'), typeValue)

        const statusValue = noteElt.querySelector('[data-note-status]').getAttribute('data-note-status')
        this.selectType.setOption(this.modalNoteElt.querySelector('#note_status'), statusValue)

        if (this.autoSave.active === false) {
            const content  = this.contentNoteElt.innerHTML
            this.CkEditor.setData(content)
            this.noteContentElt.textContent = content
            this.data = this.CkEditor.getData()
        }
        
        this.btnDeleteElt.classList.replace('d-none', 'd-block')
        this.btnDeleteElt.href = '/note/' + this.cardId + '/delete'

        this.btnExportWordElt.classList.replace('d-none', 'd-block')
        this.btnExportWordElt.href = '/note/' + this.cardId + '/export/word'
        this.btnExportPdfElt.classList.replace('d-none', 'd-block')
        this.btnExportPdfElt.href = '/note/' + this.cardId + '/export/pdf'

        this.autoSave.init()
    }

    /**
     * Envoie la requête ajax pour sauvegarder la note.
     * @param {Event} e 
     */
    requestToSave(e) {
        this.autoSave.clear(e)

        if (true === this.loader.isActive()) {
            return   
        }

        if (this.CkEditor.getData() === '') {
            return new MessageFlash('danger', 'Veuillez rédiger la note avant d\'enregistrer.')
        }

        if (this.CkEditor.getData() != this.data) {
            this.noteContentElt.textContent = this.CkEditor.getData()
        }

        if (!this.autoSave.active) {
            this.loader.on()
        }

        this.ajax.send('POST', this.formNoteElt.getAttribute('action'), this.responseAjax.bind(this), new FormData(this.formNoteElt))
    }

    autoSaveNote() {
        this.autoSaveElt.classList.add('d-block')
        setTimeout(() => {
            this.autoSaveElt.classList.remove('d-block')
        }, 5000)
        this.requestToSave()
    }

    /**
     * Envoie la requête ajax pour supprimer la note.
     * @param {Event} e 
     */
    requestToDelete(e) {
        this.autoSave.clear(e)
        if (true === this.loader.isActive()) {
            return
        }

        if (window.confirm('Voulez-vous vraiment supprimer cette note ?')) {
            this.ajax.send('GET', this.btnDeleteElt.href, this.responseAjax.bind(this))
        }
    }


    /**
     * Vérifie si des modifications ont été apportées avant la fermeture de la modal.
     * @param {Event} e 
     */
    goOutModal(e) {
        if (e.target === this.modalNoteElt && this.CkEditor.getData() != this.data) {
            if (window.confirm("Attention, vous n'avez pas enregistrer les modifications. \nContinuez sans sauvegarder ?")) {
                this.noteModalElt.hide()
            }
        }
    }

    /**
     * Réponse du serveur.
     * @param {Object} response 
     */
    responseAjax(response) {
        if (response.code === 200) {
            switch (response.action) {
                case 'create':
                    this.createNote(response.data)
                    break
                case 'update':
                    this.updateNote(response.data)
                    break
                case 'delete':
                    this.deleteNote(response.data)
                    break
            }

            if (!this.autoSave.active && response.msg) {
                new MessageFlash(response.alert, response.msg)
                this.loader.off()
            }
        }
    }

    /**
     * Crée la note dans le container.
     * @param {Object} data 
     */
    createNote(data) {
        const noteElt = document.createElement('div')
        noteElt.className = 'col-sm-12 col-lg-6 mb-4 reveal'
        noteElt.setAttribute('data-note-id', data.noteId)
        noteElt.innerHTML =
            `<div class='card h-100 shadow'>
                <div class='card-header'>
                    <h3 class='card-title h5 text-${this.themeColor}'>${this.modalNoteElt.querySelector('#note_title').value}</h3>
                    <span data-note-type="1">${data.type}</span>
                    <span data-note-status="1">(${data.status})</span>
                    <span class="small text-secondary" data-note-created="true">${data.editInfo}</span>
                    <span class="small text-secondary" data-note-updated="true"></span>
                </div>
                <div class='card-body note-content cursor-pointer' data-toggle='modal' data-target='#note-modal' data-placement='bottom' title='Modifier la note'>
                    <div class='card-text'>${this.CkEditor.getData()}</div>
                    <span class='note-fadeout'></span>
                </div>
            </div>`

        this.modalNoteElt.querySelector('form').action = '/note/' + data.noteId + '/edit'
        this.btnDeleteElt.classList.replace('d-none', 'd-block')

        this.containerNotesElt.firstChild.before(noteElt)
        // Met à jour le nombre de notes
        this.updateCounts(1)

        this.showNote(noteElt)
        // Créé l'animation d'apparition
        setTimeout(() => {
            noteElt.classList.add('reveal-on')
        }, 100)

        noteElt.addEventListener('click', () => this.showNote(noteElt))
    }

    /**
     * Met à jour la note dans le container.
     * @param {Object} data 
     */
    updateNote(data) {
        if (this.autoSave.active) {
            return
        }

        this.titleNoteElt.textContent = this.modalNoteElt.querySelector('#note_title').value
        this.contentNoteElt.innerHTML = this.CkEditor.getData()
        this.data = this.CkEditor.getData()

        const noteTypeElt = this.noteElt.querySelector('[data-note-type]')
        noteTypeElt.textContent = data.type
        noteTypeElt.setAttribute('data-note-type', this.selectType.getOption(this.modalNoteElt.querySelector('#note_type')))

        const noteStatusElt = this.noteElt.querySelector('[data-note-status]')
        noteStatusElt.textContent = '(' + data.status + ')'
        noteStatusElt.setAttribute('data-note-status', this.selectType.getOption(this.modalNoteElt.querySelector('#note_status')))

        this.noteElt.querySelector('[data-note-updated]').textContent = data.editInfo
    }

    deleteNote() {
        this.containerNotesElt.querySelector(`div[data-note-id="${this.cardId}"]`).remove()
        this.updateCounts(-1)
        this.noteModalElt.hide()
    }

    /**
     * Met à jour le compteur du nombre de notes.
     * @param {Number} nb 
     */
    updateCounts(nb) {
        this.countNotesElt.textContent = parseInt(this.countNotesElt.textContent) + nb
        if (this.nbTotalNotesElt) {
            this.nbTotalNotesElt.textContent = parseInt(this.nbTotalNotesElt.textContent) + nb
        }
        if (parseInt(this.countNotesElt.textContent) > 0) {
            return this.searchSupportNotesElt.classList.remove('d-none')
        }
        return this.searchSupportNotesElt.classList.add('d-none')
    }
}