import Ajax from "../utils/ajax";

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
}