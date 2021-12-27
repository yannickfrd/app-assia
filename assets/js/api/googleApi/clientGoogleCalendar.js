import Ajax from "../../utils/ajax";
import MessageFlash from "../../utils/messageFlash";

export default class ClientGoogleCalendar {
    constructor() {
        this.ajax = new Ajax
        this.modalRdvElt = document.getElementById('modal-rdv')
        this.btnDeleteRdvElt = this.modalRdvElt.querySelector('button#modal-btn-delete')
        this.btnSaveRdvElt = this.modalRdvElt.querySelector('button#js-btn-save')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name=rdv]')
        this.googleCalendarCheckbox = this.formRdvElt.elements['rdv_googleCalendar']
        this.urlCreateClientGoogle = this.googleCalendarCheckbox.dataset['clientGoogle']
    }

    /**
     * Create a new event on Google Calendar
     * @param {number} rdvId
     * @param {string} createUpdate
     */
    createUpdateEvent(rdvId, createUpdate) {
        switch (createUpdate) {
            case 'create':
                const createUrl = this.urlCreateClientGoogle + '?rdv_id=' + rdvId
                this.ajax.send('GET', createUrl, this.responseAjax.bind(this))
                break
            case 'update':
                const updateUrl = this.btnSaveRdvElt.dataset['updateGoogleEvent'].replace('__id__', rdvId);
                this.ajax.send('PUT', updateUrl, this.responseAjax.bind(this))
                break
        }
    }

    /**
     * Delete event on Google Calendar
     * @param {string} googleEventId
     */
    deleteEvent(googleEventId) {
        if (googleEventId) {
            const url = this.btnDeleteRdvElt.dataset['deleteGoogleEvent'].replace('__id__', googleEventId)
            this.ajax.send('DELETE', url, this.responseAjax.bind(this))
        }
    }

    /**
     * Get the answer back
     * @param data
     */
    responseAjax(data) {
        switch (data.action) {
            case 'create':
                window.open(data.url, '_blank')
                break
            case 'delete':
                new MessageFlash(data.alert, data.msg)
                break
            case 'update':
                new MessageFlash(data.alert, data.msg)
                break
        }
    }
}