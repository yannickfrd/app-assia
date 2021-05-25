import FormValidator from '../utils/form/formValidator'
import FieldDisplayer from '../utils/form/fieldDisplayer'
import SearchLocation from '../utils/searchLocation'
import ItemsListManager from '../utils/form/itemsListManager'

/**
 * Evaluation sociale.
 */
export default class evaluation {

    constructor() {
        this.formValidator = new FormValidator()
        this.init()
    }

    init() {
        document.querySelectorAll('div[data-parent-field]').forEach(elt => {
            new FieldDisplayer(elt)
        })        
        
        new ItemsListManager('evalHousingGroup_hsgHelps')
        new SearchLocation('domiciliation_location')
        
        document.getElementById('accordion-init_eval').querySelectorAll('button[data-person-key]').forEach(personElt => {
            const key = personElt.dataset.personKey
            new ItemsListManager(`${key}_evalSocialPerson_healthProblemType`)
            new SearchLocation(`school_location_${key}`, 'city')
        })
        
        
        document.getElementsByClassName('card').forEach(cardElt => {
            const btnPersonElts = cardElt.querySelectorAll('button[data-person-key]')
            btnPersonElts.forEach(btnElt => {
                btnElt.addEventListener('click', () => this.activeBtn(btnPersonElts, btnElt))
            })
        })
        
        document.querySelectorAll('input[type="date"]').forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
                this.formValidator.checkDate(dateElt, -(365 * 20), (365 * 11))
                if (!dateElt.value) {   
                    dateElt.value = ''
                }
            })
        })
    }
    
    /**
     * Active/Désactive le bouton d'une personne au clic.
     * 
     * @param {NodeList} btnElts 
     * @param {HTMLButtonElement} selectedBtnElt 
     */
    activeBtn(btnElts, selectedBtnElt) {
        let active = false
        if (selectedBtnElt.classList.contains('active')) {
            active = true
        }
        btnElts.forEach(btn => {
            btn.classList.remove('active')
        })
        if (!active) {
            selectedBtnElt.classList.add('active')
        }
    }
}