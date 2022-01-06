import Ajax from "../utils/ajax";
import MessageFlash from "../utils/messageFlash";

export default class ApiCalendar {
    constructor() {
        this.ajax = new Ajax
        this.modalRdvElt = document.getElementById('modal-rdv')
        this.btnDeleteRdvElt = this.modalRdvElt.querySelector('button#modal-btn-delete')
        this.btnSaveRdvElt = this.modalRdvElt.querySelector('button#js-btn-save')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name=rdv]')

        this.googleCalendarCheckbox = this.formRdvElt.elements['rdv_googleCalendar']
        this.googleCheckboxIsChecked = this.googleCalendarCheckbox.checked
        this.urlCreateClientGoogle = this.googleCalendarCheckbox.dataset['clientGoogle']

        this.outlookCalendarCheckbox = this.formRdvElt.elements['rdv_outlookCalendar']
        this.outlookCheckboxIsChecked = this.outlookCalendarCheckbox.checked
        this.urlCreateClientOutlook = this.outlookCalendarCheckbox.dataset['clientOutlook']
    }

    initCalendarCheckbox(key) {
        const valLocalStorage = localStorage.getItem('agenda.' + key)
        switch (key) {
            case 'google':
                this.googleCalendarCheckbox.checked = (null === valLocalStorage) ? false : JSON.parse(valLocalStorage)
                break;
            case 'outlook':
                this.outlookCalendarCheckbox.checked = (null === valLocalStorage) ? false : JSON.parse(valLocalStorage)
                break;
        }
    }

    calendarIsChecked(key) {
        const valLocalStorage = localStorage.getItem('agenda.' + key)
        return (null === valLocalStorage) ? false : JSON.parse(valLocalStorage)
    }

    execute(action, rdvId = null, eventId = null) {

        this.modalRdvElt.querySelectorAll('.api-calendar').forEach(elt => {
            if (elt.checked) {
                let url = ''
                let method = 'GET'

                switch (action) {
                    case 'create':
                        url = elt.dataset['apiCreateEvent'] + '?rdv_id=' + rdvId
                        break;
                    case 'update':
                        url = elt.dataset['apiUpdateEvent'].replace('__id__', rdvId)
                        method = 'PUT'
                        break;
                    case 'delete':
                        if (elt.name === 'rdv[outlookCalendar]' && null !== eventId.outlook) {
                            console.log('outlook')
                            url = elt.dataset['apiDeleteEvent'].replace('__id__', eventId.outlook);
                        }
                        if (elt.name === 'rdv[googleCalendar]' && null !== eventId.google) {
                            console.log('google')
                            url = elt.dataset['apiDeleteEvent'].replace('__id__', eventId.google);
                        }
                        method = 'DELETE'
                        break;
                }

                if (url) {
                    console.log(method, url);
                    this.ajax.send(method, url, this.responseAjax.bind(this))
                }
            }
        })
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