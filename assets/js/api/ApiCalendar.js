import Ajax from "../utils/ajax"
import AlertMessage from "../utils/AlertMessage"
import RdvModel from "../event/rdv/model/RdvModel"
import {Modal} from "bootstrap"
import RdvForm from "../event/rdv/RdvForm"

export default class ApiCalendar {
    /**
     * @param {RdvForm} rdvForm 
     */
    constructor(rdvForm) {
        this.initRdv = () => rdvForm.initRdv
        this.checkboxGoogleCalendar = rdvForm.checkboxGoogleCalendar
        this.checkboxOutlookCalendar = rdvForm.checkboxOutlookCalendar

        this.ajax = new Ajax

        this.modalElt = document.querySelector('#update_api_modal')
        this.updateModal = new Modal(this.modalElt)

        this.init()
    }

    init() {
        this.checkboxGoogleCalendar.addEventListener('change', (e) => {
            localStorage.setItem('calendar.google', e.currentTarget.checked)
        })
        this.checkboxOutlookCalendar.addEventListener('change', (e) => {
            localStorage.setItem('calendar.outlook', e.currentTarget.checked)
        })
    }

    /**
     * 
     * @param {Object} response 
     */
     checkResponse(response) {
        const rdv = response.rdv
        const apiUrls = response.apiUrls

        switch (response.action) {
            case 'create':
                this.addEvent(rdv, apiUrls)
                break
            case 'update':
                this.updateEvent(rdv, apiUrls)
                break
        }
    }

    /**
     * Allows to pre-fill the form automatically on Google and Outlook calendars.
     * And open a new tab.
     * 
     * @param {Object} rdv
     * @param {Object} apiUrls
     */
    addEvent(rdv, apiUrls) {
        const rdvModel = new RdvModel(rdv)

        Object.keys(apiUrls).forEach(apiName => {
            rdvModel.apiName = apiName

            window.open(rdvModel.url, '_blank')
        })
    }

    /**
     * @param {Object} rdv
     * @param {Object} apiUrls
     */
     updateEvent(rdv, apiUrls) {
        const rdvModel = new RdvModel(rdv)

        if ((this.checkboxGoogleCalendar.checked && this.initRdv().googleEventId === null)
            || (this.checkboxOutlookCalendar.checked && this.initRdv().outlookEventId === null)
            || (rdvModel.isDifferent(this.initRdv()) && (this.checkboxGoogleCalendar.checked
                || this.checkboxOutlookCalendar.checked))
        ) {
            this.updateModal.show()

            const listApis = () => {
                let list = {}

                if (this.checkboxGoogleCalendar.checked) {
                    list.google = apiUrls.google
                }
                if (this.checkboxOutlookCalendar.checked) {
                    list.outlook = apiUrls.outlook
                }

                return Object.keys(list).length === 0 ? apiUrls : list
            }

            this.modalElt.querySelector('[data-action="confirm"]').addEventListener('click', () => {
                this.addEvent(rdvModel, listApis())
            }, {once: true})
        }
    }

    /**
     * Executes the requested actions
     * 
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
                    break
                case 'delete':
                    if (!this.calendarIsChecked(apiName)) {
                        url = false
                    }
                    method = 'DELETE'
                    break
            }

            if (url) {
                this.ajax.send(method, url, this.responseAjax.bind(this))
            }
        }
    }

    /**
     * Get the answer back
     * 
     * @param {Object} data
     */
    responseAjax(data) {
        switch (data.action) {
            case 'create':
                window.open(data.url, '_blank')
                break
            case 'delete':
                new AlertMessage(data.alert, data.msg)
                break
            case 'update':
                new AlertMessage(data.alert, data.msg)
                break
        }
    }

    /**
     * @param {string} key
     * @returns {boolean}
     */
    calendarIsChecked(key) {
        const valLocalStorage = localStorage.getItem('calendar.' + key)
        
        return (null === valLocalStorage) ? false : JSON.parse(valLocalStorage)
    }
}
