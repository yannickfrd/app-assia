import ValidationPerson from './validationPerson'
import AjaxRequest from '../utils/ajaxRequest'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'

/**
 * Requête Ajax pour mettre à jour les informations individuelles.
 */
export default class UpdatePerson {

    constructor() {
        this.ajaxRequest = new AjaxRequest()
        this.personFormElt = document.querySelector('form[name=person]')
        this.updatePersonBtnElt = document.getElementById('updatePerson')
        this.loader = new Loader()
        this.init()
    }

    init() {
        const validationPerson = new ValidationPerson(
            'person_lastname',
            'person_firstname',
            'person_birthdate',
            'person_gender',
            'person_email'
        )

        if (this.updatePersonBtnElt) {
            this.updatePersonBtnElt.addEventListener('click', e => {
                e.preventDefault()
                if (validationPerson.getNbErrors() === 0) {
                    this.loader.on()
                    const formData = new FormData(this.personFormElt)
                    const formToString = new URLSearchParams(formData).toString()
                    const url = this.updatePersonBtnElt.getAttribute('data-url')
                    this.ajaxRequest.init('POST', url, this.response.bind(this), true, formToString)
                }
            })
        }
    }

    response(data) {
        const dataJSON = JSON.parse(data)

        if (dataJSON.code === 200) {
            if (dataJSON.alert === 'success') {
                document.getElementById('js-person-updated').textContent = '(modifié le ' + dataJSON.date + ' par ' + dataJSON.user + ')'
            }
        }
        this.loader.off()
        new MessageFlash(dataJSON.alert, dataJSON.msg)
    }
}