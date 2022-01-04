import ApiCalendar from "../ApiCalendar";

export default class ClientOutlookCalendar extends ApiCalendar {
    constructor() {
        super();

        this.init()
    }

    init() {
        this.outlookCalendarCheckbox.addEventListener('change', () => {
            this.outlookCheckboxIsChecked = this.outlookCalendarCheckbox.checked
        })
    }

    createEvent(rdvId) {
        if (this.outlookCheckboxIsChecked) {
            const createUrl = this.urlCreateClientOutlook + '?rdv_id=' + rdvId;

            this.ajax.send('GET', createUrl, this.responseAjax.bind(this))
        }
    }

    responseAjax(data) {
        switch (data.action) {
            case 'create':
                window.open(data.url, '_blank')
                break
        }
    }

}