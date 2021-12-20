import Ajax from "../../utils/ajax";

export default class ClientGoogleCalendar {
    constructor(rdvId) {
        this.rdvId = rdvId
        this.ajax = new Ajax
        this.modalRdvElt = document.getElementById('modal-rdv')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name=rdv]')
        this.googleCalendarCheckbox = this.formRdvElt.elements['rdv_googleCalendar']
        this.urlCreateClientGoogle = this.googleCalendarCheckbox.dataset['clientGoogle']

        this.init()
    }

    init() {
        this.ajax.send('GET', this.urlCreateClientGoogle + '?rdv_id=' + this.rdvId, this.response.bind(this))
    }

    response(url) {
        window.open(url, '_blank')
    }
}