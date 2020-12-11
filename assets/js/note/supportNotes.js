import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import AutoSave from '../utils/autoSave'
import SelectType from '../utils/selectType'
import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document'
import ParametersUrl from '../utils/parametersUrl'
import { Modal } from 'bootstrap'
// import language from '@ckeditor/ckeditor5-build-decoupled-document/build/translations/fr.js'

export default class SupportNotes {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
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
        this.btnExportWordElt = this.modalNoteElt.querySelector('#export-note-word')
        this.btnExportPdfElt = this.modalNoteElt.querySelector('#export-note-pdf')
        this.btnDeleteElt = this.modalNoteElt.querySelector('#modal-btn-delete')

        this.themeColor = document.getElementById('header').getAttribute('data-color')
        this.autoSaveElt = document.getElementById('js-auto-save')
        this.countNotesElt = document.getElementById('count-notes')
        this.nbTotalNotesElt = document.getElementById('nb-total-notes')
        this.supportId = document.getElementById('container-notes').getAttribute('data-support')
        this.editor
        this.data = null

        this.init()
        this.autoSave = new AutoSave(this.autoSaveNote.bind(this), this.editorElt, 2 * 60, 20)
    }

    init() {
        this.ckEditor()

        this.newNoteBtn.addEventListener('click', this.newNote.bind(this))

        this.noteElts.forEach(noteElt => {
            noteElt.addEventListener('click', this.getNote.bind(this, noteElt))
        })

        this.btnSaveElt.addEventListener('click', e => {
            this.autoSave.clear(e)
            if (this.loader.isActive() === false) {
                this.saveNote()
            }
        })

        this.btnCancelElt.addEventListener('click', e => this.autoSave.clear(e))

        this.btnDeleteElt.addEventListener('click', e => {
            this.autoSave.clear(e)
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
        this.btnExportWordElt.classList.replace('d-block', 'd-none')
        this.btnExportPdfElt.classList.replace('d-block', 'd-none')
        this.autoSave.init()
    }


    /**
     * Donne la note sélectionnée dans le formulaire modal.
     * @param {HTMLElement} noteElt 
     */
    getNote(noteElt) {
        this.modalElt.show()

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

        if (this.autoSave.active === false) {
            const content  = this.contentNoteElt.innerHTML
            this.editor.setData(content)
            this.noteContentElt.textContent = content
            this.data = this.editor.getData()
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
     */
    saveNote() {
        if (this.editor.getData() === '') {
            return new MessageFlash('danger', 'Veuillez rédiger la note avant d\'enregistrer.')
        }

        if (this.editor.getData() != this.data) {
            this.noteContentElt.textContent = this.editor.getData()
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
        this.saveNote()
    }

    /**
     * Envoie la requête ajax pour supprimer la note.
     */
    deleteNote() {
        if (window.confirm('Voulez-vous vraiment supprimer cette note ?')) {
            this.ajax.send('GET', this.btnDeleteElt.href, this.responseAjax.bind(this))
        }
    }


    /**
     * Vérifie si des modifications ont été apportées avant la fermeture de la modal.
     * @param {Event} e 
     */
    goOutModal(e) {
        if (e.target === this.modalNoteElt && this.editor.getData() != this.data) {
            if (window.confirm("Attention, vous n'avez pas enregistrer les modifications. \nContinuez sans sauvegarder ?")) {
                this.modalElt.hide()
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
                    if (!this.autoSave.active) {
                        this.updateNote(response.data)
                    }
                    break
                case 'delete':
                    document.getElementById('note-' + this.cardId).remove()
                    this.updateCounts(-1)
                    this.modalElt.hide()
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
        this.data = this.editor.getData()

        const noteTypeElt = this.noteElt.querySelector('.js-note-type')
        noteTypeElt.textContent = data.type

        noteTypeElt.setAttribute('data-value', this.selectType.getOption(this.modalNoteElt.querySelector('#note_type')))

        const noteStatusElt = this.noteElt.querySelector('.js-note-status')
        noteStatusElt.textContent = '(' + data.status + ')'
        noteStatusElt.setAttribute('data-value', this.selectType.getOption(this.modalNoteElt.querySelector('#note_status')))

        this.noteElt.querySelector('.js-note-updated').textContent = data.editInfo
    }

    /**
     * Met à jour le compteur du nombre de notes.
     * @param {Number} value 
     */
    updateCounts(value) {
        this.countNotesElt.textContent = parseInt(this.countNotesElt.textContent) + value
        if (this.nbTotalNotesElt) {
            this.nbTotalNotesElt.textContent = parseInt(this.nbTotalNotesElt.textContent) + value
        }
    }
}