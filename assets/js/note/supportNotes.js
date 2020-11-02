import AjaxRequest from '../utils/ajaxRequest'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import SelectType from '../utils/selectType'
import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document'
import ParametersUrl from '../utils/parametersUrl'
import { Modal } from 'bootstrap'
// import language from '@ckeditor/ckeditor5-build-decoupled-document/build/translations/fr.js'

export default class SupportNotes {

    constructor() {
        this.loader = new Loader()
        this.ajaxRequest = new AjaxRequest(this.loader)
        this.selectType = new SelectType()
        this.parametersUrl = new ParametersUrl()
        this.modalElt = new Modal(document.getElementById('modal-note'))

        this.newNoteBtn = document.getElementById('js-new-note')
        this.noteElts = document.querySelectorAll('.js-note')

        this.modalNoteElt = document.getElementById('modal-note')
        this.formNoteElt = this.modalNoteElt.querySelector('form[name=note]')
        this.noteContentElt = this.modalNoteElt.querySelector('#note_content')
        this.editorElt = this.modalNoteElt.querySelector('#editor')
        this.btnSaveElt = this.modalNoteElt.querySelector('#js-btn-save')
        this.btnCancelElt = this.modalNoteElt.querySelector('#js-btn-cancel')
        this.btnExportElt = this.modalNoteElt.querySelector('#js-btn-export')
        this.btnDeleteElt = this.modalNoteElt.querySelector('#modal-btn-delete')

        this.themeColor = document.getElementById('header').getAttribute('data-color')
        this.autoSaveElt = document.getElementById('js-auto-save')
        this.countNotesElt = document.getElementById('count-notes')
        this.nbTotalNotesElt = document.getElementById('nb-total-notes')
        this.supportId = document.getElementById('container-notes').getAttribute('data-support')
        this.autoSave = false
        this.count = 0
        this.editor

        this.init()
    }

    init() {
        this.ckEditor()

        this.newNoteBtn.addEventListener('click', this.newNote.bind(this))

        this.noteElts.forEach(noteElt => {
            noteElt.addEventListener('click', this.getNote.bind(this, noteElt))
        })

        this.btnSaveElt.addEventListener('click', e => {
            this.clearTimer(e)
            if (this.loader.isActive() === false) {
                this.saveNote()
            }
        })

        this.btnCancelElt.addEventListener('click', e => this.clearTimer(e))

        this.btnDeleteElt.addEventListener('click', e => {
            this.clearTimer(e)
            if (this.loader.isActive() === false) {
                this.deleteNote()
            }
        })

        const noteElt = document.getElementById('note-' + Number(this.parametersUrl.get('noteId')))
        if (noteElt) {
            this.modalElt.show()
            setTimeout(() => {
                this.getNote(noteElt)
            }, 200)
        }

        this.modalNoteElt.addEventListener('mousedown', e => this.goOutModal(e))
    }
    
    /**
     * Vérifie si des modifications ont été apportées avant la fermeture de la modal.
     * @param {Event} e 
     */
    goOutModal(e) {
        if (e.target === this.modalNoteElt && this.count > 0) {
            if (window.confirm("Attention, vous n'avez pas enregistrer les modifications.\n Voulez-vous les enregistrer ?")) {
                this.saveNote()
            }
        }
    }

    /**
     * Initialise CKEditor.
     */
    ckEditor() {
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
                console.error(error)
            })
    }

    /**
     * Affiche un formulaire modal vierge.
     */
    newNote() {
        this.modalElt.show()

        this.modalNoteElt.querySelector('form').action = '/support/' + this.supportId + '/note/new'
        this.modalNoteElt.querySelector('#note_title').value = ''
        this.selectType.setOption(this.modalNoteElt.querySelector('#note_type'), 1)
        this.selectType.setOption(this.modalNoteElt.querySelector('#note_status'), 1)
        this.editor.setData('')
        this.btnDeleteElt.classList.replace('d-block', 'd-none')
        this.btnExportElt.classList.replace('d-block', 'd-none')
        this.editorElt.addEventListener('keydown', this.countKeyDown.bind(this))
        this.timerAutoSave()
    }


    /**
     * Donne la note sélectionnée dans le formulaire modal.
     * @param {HTMLElement} noteElt 
     */
    getNote(noteElt) {
        this.modalElt.show();

        this.noteElt = noteElt
        this.contentNoteElt = noteElt.querySelector('.card-text')

        this.cardId = Number(noteElt.id.replace('note-', ''))
        this.modalNoteElt.querySelector('form').action = '/note/' + this.cardId + '/edit'

        this.titleNoteElt = noteElt.querySelector('.card-title')
        this.modalNoteElt.querySelector('#note_title').value = this.titleNoteElt.textContent

        const typeValue = noteElt.querySelector('.js-note-type').getAttribute('data-value')
        this.selectType.setOption(this.modalNoteElt.querySelector('#note_type'), typeValue)

        const statusValue = noteElt.querySelector('.js-note-status').getAttribute('data-value')
        this.selectType.setOption(this.modalNoteElt.querySelector('#note_status'), statusValue)

        if (this.autoSave === false) {
            this.editor.setData(this.contentNoteElt.innerHTML)
        }

        this.btnDeleteElt.classList.replace('d-none', 'd-block')
        this.btnDeleteElt.href = '/note/' + this.cardId + '/delete'

        this.btnExportElt.classList.replace('d-none', 'd-block')
        this.btnExportElt.href = '/note/' + this.cardId + '/export'

        this.editorElt.addEventListener('keydown', this.countKeyDown.bind(this))

        this.timerAutoSave()
    }

    /**
     * Compte le nombre de saisie dans l'éditeur de texte.
     */
    countKeyDown() {
        this.count++
    }

    /**
     * Timer pour la sauvegarde automatique.
     */
    timerAutoSave() {
        clearInterval(this.countdownID)
        this.countdownID = setTimeout(this.timerAutoSave.bind(this), 2 * 60 * 1000) // toutes les 2 minutes
        if (this.count > 10) {
            this.autoSave = true
            this.count = 0
            this.autoSaveElt.classList.add('d-block')
            setTimeout(() => {
                this.autoSaveElt.classList.remove('d-block')
            }, 5000)
            this.saveNote()
        }
    }

    /**
     * Remet à zéro le timer.
     * @param {Event} e 
     */
    clearTimer(e) {
        e.preventDefault()
        this.autoSave = false
        this.count = 0
        clearInterval(this.countdownID)
    }

    /**
     * Envoie la requête ajax pour sauvegarder la note.
     */
    saveNote() {
        if (this.editor.getData() === '') {
            return new MessageFlash('danger', 'Veuillez rédiger la note avant d\'enregistrer.')
        }

        this.noteContentElt.textContent = this.editor.getData()
        const formData = new FormData(this.formNoteElt)

        if (!this.autoSave) {
            this.loader.on()
        }

        this.ajaxRequest.send('POST', this.formNoteElt.getAttribute('action'), this.responseAjax.bind(this), true, new URLSearchParams(formData))
    }

    /**
     * Envoie la requête ajax pour supprimer la note.
     */
    deleteNote() {
        if (window.confirm('Voulez-vous vraiment supprimer cette note ?')) {
            this.loader.on()
            this.ajaxRequest.send('GET', this.btnDeleteElt.href, this.responseAjax.bind(this), true, null)
        }
    }

    /**
     * Réponse du serveur
     * @param {Object} response 
     */
    responseAjax(response) {

        if (response.code === 200) {
            switch (response.action) {
                case 'create':
                    this.createNote(response.data)
                    break
                case 'update':
                    if (!this.autoSave) {
                        this.updateNote(response.data)
                    }
                    break
                case 'delete':
                    document.getElementById('note-' + this.cardId).remove()
                    this.updateCounts(-1)
                    this.modalElt.hide();
                    break
            }

            if (!this.autoSave) {
                this.modalElt.hide();
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
        noteElt.id = 'note-' + data.noteId
        this.modalNoteElt.querySelector('form').action = '/note/' + data.noteId + '/edit'
        this.btnDeleteElt.classList.replace('d-none', 'd-block')

        noteElt.className = 'col-sm-12 col-lg-6 mb-4 js-note reveal'
        noteElt.innerHTML =
            `<div class='card h-100 shadow'>
                <div class='card-header'>
                    <h3 class='card-title h5 text-${this.themeColor}'>${this.modalNoteElt.querySelector('#note_title').value}</h3>
                    <span class='js-note-type' data-value='1'>${data.type}</span>
                    <span class='js-note-status' data-value='1'>(${data.status})</span>
                    <span class='small text-secondary js-note-created'>${data.editInfo}</span>
                    <span class='small text-secondary js-note-updated'></span>
                </div>
                <div class='card-body note-content cursor-pointer' data-toggle='modal' data-target='#modal-note' data-placement='bottom' title='Modifier la note'>
                    <div class='card-text'>${this.editor.getData()}</div>
                    <span class='note-fadeout'></span>
                </div>
            </div>`

        const containerNotesElt = document.getElementById('container-notes')
        containerNotesElt.insertBefore(noteElt, containerNotesElt.firstChild)
        // Met à jour le nombre de notes
        this.updateCounts(1)

        this.getNote(noteElt)
        // Créé l'animation d'apparition
        setTimeout(() => {
            noteElt.classList.add('reveal-on')
        }, 100)

        noteElt.addEventListener('click', this.getNote.bind(this, noteElt))
    }

    /**
     * Met à jour la note dans le container.
     * @param {Object} data 
     */
    updateNote(data) {
        this.titleNoteElt.textContent = this.modalNoteElt.querySelector('#note_title').value
        this.contentNoteElt.innerHTML = this.editor.getData()

        const noteTypeElt = this.noteElt.querySelector('.js-note-type')
        noteTypeElt.textContent = data.type

        noteTypeElt.setAttribute('data-value', this.selectType.getOption(this.modalNoteElt.querySelector('#note_type')))

        const noteStatusElt = this.noteElt.querySelector('.js-note-status')
        noteStatusElt.textContent = '(' + data.status + ')'
        noteStatusElt.setAttribute('data-value', this.selectType.getOption(this.modalNoteElt.querySelector('#note_status')))

        this.noteElt.querySelector('.js-note-updated').textContent = data.editInfo
    }

    /**
     * Met à jour le copteur du nombre de notes.
     * @param {Number} value 
     */
    updateCounts(value) {
        this.countNotesElt.textContent = parseInt(this.countNotesElt.textContent) + value
        if (this.nbTotalNotesElt) {
            this.nbTotalNotesElt.textContent = parseInt(this.nbTotalNotesElt.textContent) + value
        }
    }
}