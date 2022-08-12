import AbstractManager from '../../AbstractManager'
import RdvForm from './RdvForm'
import ApiCalendar from '../../api/ApiCalendar'
import AlertMessage from '../../utils/AlertMessage'

export default class RdvManager extends AbstractManager {

    constructor() {
        super('rdv', null, {backdrop: 'static', keyboard: false})

        this.form = new RdvForm(this)
        this.apiCalendar = new ApiCalendar(this.form)
    }

    /**
     * @param {Object} response
     */
    responseAjax(response) {
        this.checkActions(response, response.rdv)
        this.apiCalendar.checkResponse(response)

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        this.objectModal.hide()
    }
}