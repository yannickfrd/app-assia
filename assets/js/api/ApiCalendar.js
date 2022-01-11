import Ajax from "../utils/ajax";
import MessageFlash from "../utils/messageFlash";

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
     * Executes the requested actions
     * @param {string} action
     * @param {Object} apiUrls
     * @param {number|null} rdvId
     * @param {string|null} eventId
     */
    execute(action, apiUrls, rdvId = null, eventId = null) {
        for (const [apiName, apiUrl] of Object.entries(apiUrls)) {
            let url = ''
            let method = 'GET'

            switch (action) {
                case 'create':
                    url = apiUrl + '?rdv_id=' + rdvId
                    break;
                case 'update':
                    url = apiUrl.replace('__id__', rdvId)
                    method = 'PUT'
                    break;
                case 'delete':
                    if (null !== eventId[apiName] && this.calendarIsChecked(apiName)) {
                        url = apiUrl.replace('__id__', eventId[apiName]);
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

    /**
     * @param {string} key
     * @returns {boolean}
     */
    calendarIsChecked(key) {
        const valLocalStorage = localStorage.getItem('agenda.' + key)
        return (null === valLocalStorage) ? false : JSON.parse(valLocalStorage)
    }
}