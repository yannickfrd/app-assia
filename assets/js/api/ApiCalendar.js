import Ajax from "../utils/ajax";
import MessageFlash from "../utils/messageFlash";

export default class ApiCalendar {
    constructor() {
        this.ajax = new Ajax

        this.modalRdvElt = document.getElementById('modal-rdv')
        this.btnDeleteRdvElt = this.modalRdvElt.querySelector('button#modal-btn-delete')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name=rdv]')

        this.googleCalendarCheckbox = this.modalRdvElt.querySelector('input[name="rdv[_googleCalendar]"]')
        this.outlookCalendarCheckbox = this.modalRdvElt.querySelector('input[name="rdv[_outlookCalendar]"]')

        this.init()
    }

    init() {
        this.googleCalendarCheckbox.addEventListener('change', (e) => {
            localStorage.setItem('calendar.google', e.currentTarget.checked)
        })
        this.outlookCalendarCheckbox.addEventListener('change', (e) => {
            localStorage.setItem('calendar.outlook', e.currentTarget.checked)
        })
    }

    /**
     * Allows to pre-fill the form automatically on Google and Outlook calendars.
     * And open a new tab.
     * @param {RdvModel} rdvMdl
     * @param {Object} api
     */
    addEvent(rdvMdl, api) {
        Object.keys(api).forEach(apiName => {
            rdvMdl.apiName = apiName

            window.open(rdvMdl.url, '_blank')
        })
    }

    /**
     * Executes the requested actions
     * @param {string} action
     * @param {Object} apiUrls
     */
    execute(action, apiUrls) {
        for (const [apiName, apiUrl] of Object.entries(apiUrls)) {
            let url = apiUrl
            let method = 'GET'

            switch (action) {
                case 'update':
                    method = 'PUT'
                    break;
                case 'delete':
                    if (!this.calendarIsChecked(apiName)) {
                        url = false;
                    }
                    method = 'DELETE'
                    break;
            }

            if (url) {
                this.ajax.send(method, url, this.responseAjax.bind(this))
            }
        }
    }

    /**
     * Get the answer back
     * @param {Object} data
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

    /**
     * @param {string} key
     * @returns {boolean}
     */
    calendarIsChecked(key) {
        const valLocalStorage = localStorage.getItem('agenda.' + key)
        
        return (null === valLocalStorage) ? false : JSON.parse(valLocalStorage)
    }
}
