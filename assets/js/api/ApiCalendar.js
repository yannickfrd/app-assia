import Ajax from "../utils/ajax";
import MessageFlash from "../utils/messageFlash";
import RdvModel from "../rdv/model/RdvModel";

export default class ApiCalendar {
    constructor() {
        this.ajax = new Ajax

        this.modalRdvElt = document.getElementById('modal-rdv')
        this.btnDeleteRdvElt = this.modalRdvElt.querySelector('button#modal-btn-delete')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name=rdv]')
        this.listApiCalendarCheckbox = this.modalRdvElt.querySelectorAll('.api-calendar')

        this.init()
    }

    init() {
        this.listApiCalendarCheckbox.forEach(elt => {
            const apiName = elt.dataset['apiName']
            const storageKey = 'agenda.' + apiName
            const valLocalStorage = localStorage.getItem(storageKey)

            elt.checked = (null === valLocalStorage) ? false : JSON.parse(valLocalStorage)

            elt.addEventListener('change', e => localStorage.setItem(storageKey, e.currentTarget.checked))
        })
    }

    /**
     * Allows to pre-fill the form automatically on Google and Outlook calendars.
     * And open a new tab.
     * @param {Object} rdv
     * @param {Object} api
     */
    executeJs(rdv, api) {
        Object.keys(api).forEach(apiName => window.open((new RdvModel(apiName, rdv)).url, '_blank'))
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
