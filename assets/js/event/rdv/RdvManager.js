import AbstractManager from '../../AbstractManager'
import RdvForm from './RdvForm'
import RdvModel from './model/RdvModel'
import ApiCalendar from '../../api/ApiCalendar'
import AlertMessage from '../../utils/AlertMessage'

export default class RdvManager extends AbstractManager {

    constructor() {
        super('rdv')

        this.form = new RdvForm(this)
        this.apiCalendar = new ApiCalendar()
    }

    /**
     * @param {Object} response
     */
    responseAjax(response) {
        const rdv = response.rdv
        const apiUrls = response.apiUrls

        this.checkActions(response, rdv)

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        if (apiUrls) {
            switch (response.action) {
                case 'create':
                    this.apiCalendar.addEvent(new RdvModel(rdv), apiUrls)
                    break
                case 'update':
                    this.form.updateApiRdv(rdv, apiUrls)
                    break
                // case 'delete':
                //     this.apiCalendar.execute('delete', apiUrls)
                //     break
            }
        }
        this.objectModal.hide()
    }
}