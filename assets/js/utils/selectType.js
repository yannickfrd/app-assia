/**
 * Permet d'obtenir ou de définir l'option d'un élément <select>
 */
export default class SelectType {

    /**
     * Donne la valeur de l'option sélectionnée.
     * @param {HTMLSelectElement} selectElt 
     * @return {Number}
     */
    getOption(selectElt) {
        if (selectElt === null) {
            console.error('selectElt is null !')
            return
        }
        let value = false
        selectElt.querySelectorAll('option').forEach(optionElt => {
            if (optionElt.selected || optionElt.selected === true) {
                value = parseInt(optionElt.value)
            }
        })

        return value
    }

    /**
     * Définie l'option sélectionnée
     * @param {HTMLSelectElement} selectElt 
     * @param {String} value 
     */
    setOption(selectElt, value) {
        selectElt.querySelectorAll('option').forEach(optionElt => {
            if (parseInt(optionElt.value) === parseInt(value)) {
                return optionElt.selected = true
            }
            return optionElt.selected = false
        })
    }

}