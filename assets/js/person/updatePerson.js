import ValidationPerson from './validationPerson'
import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'

/**
 * Requête Ajax pour mettre à jour les informations individuelles.
 */
export default class UpdatePerson {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.personFormElt = document.querySelector('form[name=person]')
        this.updatePersonBtnElt = document.getElementById('updatePerson')
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
                    const url = this.updatePersonBtnElt.getAttribute('data-url')
                    this.ajax.send('POST', url, this.response.bind(this), new FormData(this.personFormElt))
                }
            })
        }
    }

    /**
     * Donne la réponse à la requête Ajax.
     * @param {Object} data 
     */
    response(data) {
        console.log(data)
        if (data.code === 200) {
            if (data.alert === 'success') {
                document.getElementById('js-person-updated').textContent = `(modifié le ${data.date} par ${data.user}')`
            }
        }
        this.loader.off()
        new MessageFlash(data.alert, data.msg)
    }
}