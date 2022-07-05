import SelectManager from '../utils/form/SelectManager'

/**
 * Permet de gérer le select (avec la liste déroulante des pays) non mappée et l'input (texte) correspondant mappé. 
 */
export default class CountryManager {
    constructor() {
        this.init()
    }

    init() {
        document.querySelectorAll('div[data-country]').forEach(divElt => {
            const inputElt = divElt.querySelector('input')
            const selectElt = divElt.querySelector('select')
            
            if (!selectElt) {
                return
            }

            const selectManager = new SelectManager('#' + selectElt.id)
    
            selectElt.querySelectorAll('option').forEach(option => {
                if (option.textContent == inputElt.value) {
                    selectManager.addItem(option.value)
                }
            })
    
            selectElt.addEventListener('change', () => {
                inputElt.value = selectManager.getOption().textContent
            })
        } )
    }
}