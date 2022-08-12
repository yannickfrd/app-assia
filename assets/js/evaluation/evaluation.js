import FormValidator from '../utils/form/formValidator'
import FieldDisplayer from '../utils/form/fieldDisplayer'
import SelectManager from "../utils/form/SelectManager";
import LocationSearcher from '../utils/LocationSearcher'
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
        
        new ItemsListManager('evaluation_evalHousingGroup__hsgHelps')
        
        document.getElementById('accordion_eval_init').querySelectorAll('button[data-person-key]').forEach(personElt => {
            const key = personElt.dataset.personKey
            new ItemsListManager(`evaluation_evaluationPeople_${key}_evalSocialPerson__healthProblemType`)
        })
        
        document.querySelectorAll('.accordion-item .accordion-body').forEach(accordionBodyElt => {
            const btnPersonElts = accordionBodyElt.querySelectorAll('button[data-person-key]')
            btnPersonElts.forEach(btnElt => {
                btnElt.addEventListener('click', () => {this.activeBtn(btnElt, btnPersonElts)})
            })
        })
        
        document.querySelectorAll('input[type="date"]').forEach(dateElt => {
            dateElt.addEventListener('focusout', () => {
              this.formValidator.isValidDate(dateElt, -(365 * 99), (365 * 20))
              if (!dateElt.value) {   
                  dateElt.value = ''
              }
            })
        })

        document.querySelectorAll('[autocomplete="true"]').forEach(elt => new SelectManager(elt))
        document.querySelectorAll('[data-location-search]').forEach(elt => new LocationSearcher(elt))
    }
    
    /**
     * Active/Désactive le bouton d'une personne au clic.
     * 
     * @param {HTMLButtonElement} selectedBtnElt 
     * @param {NodeList} btnElts 
     */
    activeBtn(selectedBtnElt, btnElts) {
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