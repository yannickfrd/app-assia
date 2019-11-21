import DisplayInputs from "../utils/displayInputs";

// Situation sociale
export default class sitSocial {

    constructor() {
        this.prefix = "support_grp_sitSocial_";
        this.speSelectElt = document.getElementById("js-specifities");
        this.speCheckboxElts = document.querySelectorAll(".js-spe");
        this.init();

    }

    init() {
        new DisplayInputs(this.prefix, "speAnimal", "checkbox");
        new DisplayInputs(this.prefix, "speOther", "checkbox");

        this.speSelectElt.addEventListener("input", this.specifities.bind(this));

        this.speCheckboxElts.forEach(checkbox => {
            checkbox.addEventListener("click", function () {
                if (!checkbox.querySelector("input").checked) {
                    checkbox.classList.replace("d-block", "d-none");
                }
            }.bind(this));
            if (!checkbox.querySelector("input").checked) {
                checkbox.classList.replace("d-block", "d-none");
            }
        })
    }

    specifities() {
        let speOtionElts = this.speSelectElt.querySelectorAll("option");
        speOtionElts.forEach(option => {
            if (option.selected) {
                let speSelectedOptionElt = document.getElementById("js-spe-" + option.value);
                speSelectedOptionElt.querySelector("input").checked = "checked";
                speSelectedOptionElt.classList.replace("d-none", "d-block");
                if (speSelectedOptionElt.id === "js-spe-1") {
                    new DisplayInputs(this.prefix, "speAnimal", "checkbox");
                }
                if (speSelectedOptionElt.id === "js-spe-98") {
                    new DisplayInputs(this.prefix, "speOther", "checkbox");
                }
            }
        });
    }
}