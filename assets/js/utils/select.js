/**
 * Permet d'obtenir ou de définir l'option d'un élément <select>
 */
export default class Select {

    /**
     * Donne la valeur de l'option sélectionnée.
     * @param {HTMLElement} selectElt 
     */
    getOption(selectElt) {
        if (selectElt === null) {
            console.error('selectElt is null !')
            return
        }
        let value = false
        selectElt.querySelectorAll('option').forEach(option => {
            if (option.selected || option.selected === true) {
                value = parseInt(option.value)
            }
        })

        return value
    }

    /**
     * Définie l'option sélectionnée
     * @param {HTMLElement} selectElt 
     * @param {String} value 
     */
    setOption(selectElt, value) {
        selectElt.querySelectorAll('option').forEach(option => {
            if (parseInt(option.value) === parseInt(value)) {
                // console.log(option.value)
                return option.selected = true
            }
            return option.selected = false
        })
    }

}