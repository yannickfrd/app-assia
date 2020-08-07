// Permet d'obtenir ou de définir l'option d'un élément <select>
export default class Select {

    // Donne la valeur de l'option sélectionnée
    getOption(selectElt) {
        // console.log(selectElt);
        if (selectElt == null) {
            console.error('selectElt is null !');
            return;
        }
        let value = false;
        selectElt.querySelectorAll("option").forEach(option => {
            if (option.selected || option.selected === true) {
                value = parseInt(option.value);
            }
        });
        // console.log(value);

        return value;
    }

    // Définie l'option sélectionnée
    setOption(selectElt, value) {
        selectElt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === parseInt(value)) {
                // console.log(option.value);
                return option.selected = true;
            }
            return option.selected = false;
        });
    }

}