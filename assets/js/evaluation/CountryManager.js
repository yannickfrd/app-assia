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

            const selectManager = new SelectManager('#' + selectElt.id, {}, {
                theme: 'bootstrap4',
                'language': {
                    'noResults': () => 'Aucun résultat',
                },
            })
    
            selectElt.querySelectorAll('option').forEach(option => {
                if (option.textContent == inputElt.value) {
                    selectManager.select2.val(option.value).trigger('change')
                }
            })
    
            selectManager.select2.on('change', () => {
                inputElt.value = selectElt.querySelector(`option[value="${selectElt.value }"]`).textContent
            })
        } )
    }
}