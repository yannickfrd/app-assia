import AbstractManager from '../AbstractManager'
import NoteForm from './NoteForm'
import AlertMessage from '../utils/AlertMessage'
import StringFormatter from '../utils/string/StringFormatter'

export default class NoteManager extends AbstractManager  {

    constructor() {
        super('note', null, {backdrop: 'static', keyboard: false})

        this.form = new NoteForm(this)

        // Additionnal requests
        this.requestExportWord = (id) => this.request('export-word', id ?? this.objectId)
        this.requestExportPdf = (id) => this.request('export-pdf', id ?? this.objectId)

        this.init()
    }

    init() {
        this.isCardNoteView = Boolean(document.querySelector('div.container[data-view="card-table"]').dataset.isCard)
    }

    /**
     * @param response
     */
    responseAjax(response) {
        switch (response.action) {
            case 'download':
                return this.getFile(response.data)
        }

        const note = response.note

        this.checkActions(response, note, false)

        if (!this.form.autoSaver.active && response.msg) {
            new AlertMessage(response.alert, response.msg)
        }
        this.form.autoSaver.clear()
    }

    extraUpdatesElt(note, noteElt) {
        const content = this.form.ckEditor.getData().replace(/(<([^>]+)>)/gi, ' ')

        if (this.isCardNoteView) {
            setTimeout(() => noteElt.classList.add('reveal-on'), 50) // Add animation 'reveal'
            this.findEltByDataObjectKey(noteElt, 'content').innerHTML = new StringFormatter().slice(content, 600)
            if (note.createdAtToString === note.updatedAtToString) {
                this.findEltByDataObjectKey(noteElt, 'createdBy').innerHTML =  note.updatedByToString
            } else {
                this.findEltByDataObjectKey(noteElt, 'updateInfo').classList.remove('d-none')
            }
        } else {
            this.findEltByDataObjectKey(noteElt, 'content').innerHTML = new StringFormatter().slice(content, 200)
        }
    }
}
