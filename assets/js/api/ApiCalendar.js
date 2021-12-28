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
    }
}