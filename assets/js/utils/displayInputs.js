// Masque ou rend visible les champs Input dépendants d'un input parent
export default class DisplayInputs {

    constructor(prefix, idInput, typeInput, optionValues) {
        this.inputElt = document.getElementById(prefix + idInput);
        this.childrenElts = document.querySelectorAll(".js-" + idInput);
        this.typeInput = typeInput;
        this.optionValues = optionValues;
        this.init();
    }

    init() {
        if (this.inputElt) {
            switch (this.typeInput) {
                case "select":
                    this.select();
                    this.inputElt.addEventListener("input", this.select.bind(this));
                    break;
                case "checkbox":
                    this.checkbox();
                    this.inputElt.addEventListener("input", this.checkbox.bind(this));
                    break;
                default:
                    break;
            }
        }
    }

    // Vérifie le champ de type Checkbox
    checkbox() {
        let visible = false;
        if (this.inputElt.checked === true) {
            visible = true;
        }
        this.editchildrenElts(visible);
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
}