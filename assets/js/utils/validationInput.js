// Message de validité d'un champ de formulaire
export default class ValidationInput {
    // Met le champ en valide 
    valid(inputElt) {
        this.removeInvalidFeedbackElt(this.getlabel(inputElt));
        inputElt.classList.remove("is-valid");
        inputElt.classList.remove("is-invalid");
        if (inputElt.value) {
            inputElt.classList.add("is-valid");
        }
    }

    // Met le champ en invalide et indique un message d'erreur.
    invalid(inputElt, msg = "Saisie incorrecte.") {
        let labelElt = this.getlabel(inputElt);

        inputElt.classList.remove("is-valid");
        inputElt.classList.add("is-invalid");

        this.removeInvalidFeedbackElt(labelElt);

        labelElt.appendChild(this.createInvalidFeedbackElt(msg));
    }

    // Crée l'élément avec l'information de l'erreur.
    createInvalidFeedbackElt(msg) {
        let elt = document.createElement("div");
        elt.className = "invalid-feedback d-block js-invalid";
        elt.innerHTML = `
                <span class="form-error-icon badge badge-danger text-uppercase">Erreur</span> 
                <span class="form-error-message">${msg}</span>
                `
        return elt;
    }

    getlabel(inputElt) {
        let labelElt = inputElt.parentNode.parentNode.querySelector("label");
        if (labelElt) {
            return labelElt;
        }
        return inputElt.parentNode;
    }

    removeInvalidFeedbackElt(labelElt) {
        let invalidFeedbackElt = labelElt.querySelector("div.js-invalid");
        if (invalidFeedbackElt) {
            invalidFeedbackElt.remove();
        }
    }

    // Renvoie le nombre de champs invalides.
    getNbErrors() {
        let nbErrors = document.querySelectorAll(".js-invalid").length;

        if (nbErrors > 0) {
            console.error(nbErrors + " error(s)");
        }

        return nbErrors;
    }

    // // Vérifie si l'adresse email est valide.
    // emailIsValid(inputElt) {
    //     if (inputElt.value === "" || inputElt.value.match("^[a-z0-9._-]+@[a-z0-9._-]{2,}\\.[a-z]{2,4}")) {
    //         return true;
    //     }
    //     return false;
    // }
}