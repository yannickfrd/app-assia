import MessageFlash from "../../utils/messageFlash";
import ApiCalendar from "../ApiCalendar";

export default class ClientGoogleCalendar extends ApiCalendar {
    constructor() {
        super();

        this.initCalendarCheckbox('google')
        this.init()
    }

    init() {
        this.googleCalendarCheckbox.addEventListener('change', () => {
            localStorage.setItem('agenda.google', this.googleCalendarCheckbox.checked)
        })

    }

    /**
     * Create a new event on Google Calendar
     * @param {number} rdvId
     * @param {string} action
     */
    createUpdateEvent(rdvId, action) {
        switch (action) {
            case 'create':
                if (this.calendarIsChecked('google')) {
                    const createUrl = this.urlCreateClientGoogle + '?rdv_id=' + rdvId

                    this.ajax.send('GET', createUrl, this.responseAjax.bind(this))
                }
                break;
            case 'update':
                const updateUrl = this.btnSaveRdvElt.dataset['updateGoogleEvent'].replace('__id__', rdvId)

                this.ajax.send('PUT', updateUrl, this.responseAjax.bind(this))
                break;
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