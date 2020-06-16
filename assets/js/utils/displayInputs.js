// Masque ou rend visible les champs Input dépendants d'un input parent
export default class DisplayInputs {

    constructor(prefix, inputId, typeInput, optionValues) {
        this.inputElt = document.getElementById(prefix + inputId);
        this.childrenElts = document.querySelectorAll(".js-" + inputId);
        this.typeInput = typeInput;
        this.optionValues = optionValues;
        this.init();
    }

    init() {
        if (this.inputElt) {
            this.select();
            this.inputElt.addEventListener("change", this.select.bind(this)) // au changement sur mobile
            this.inputElt.addEventListener("click", this.select.bind(this)) // au click sur ordinateur 
        }
    }

    // Vérifie le champ de type Select
    select() {
        let visible = false;
        this.optionValues.forEach(optionValue => {
            if (this.getSelectedOption(this.inputElt) === optionValue) {
                visible = true;
            }
        });
        this.editchildrenElts(visible);
    }

    // Donne l'option sélectionnée d'un Select
    getSelectedOption() {
        let selectedOption;
        this.inputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                selectedOption = parseInt(option.value);
            }
        });
        return selectedOption;
    }

    // Masque ou rend visible les champs dépendants de l'Input principal
    editchildrenElts(visible) {
        this.childrenElts.forEach(elt => {
            if (visible === true) {
                elt.classList.replace("d-none", "d-block");
            } else {
                elt.classList.replace("d-block", "d-none");
            }
        });
    }

    // Vérifie le champ de type Checkbox
    // checkbox() {
    //     let visible = false;
    //     if (this.inputElt.checked === true) {
    //         visible = true;
    //     }
    //     this.editchildrenElts(visible);
    // }
}