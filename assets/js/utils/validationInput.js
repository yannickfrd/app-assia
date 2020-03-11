// Message de validité d'un champ de formulaire
export default class ValidationInput {

    // Met le champ en valide 
    valid(inputElt) {
        this.removeInvalidFeedbackElt(this.getlabel(inputElt));
        inputElt.classList.remove("is-invalid");
        if (inputElt.value) {
            inputElt.classList.add("is-valid");
        }
    }

    // Met le champ en invalide et indique un message d'erreur
    invalid(inputElt, msg) {
        let labelElt = this.getlabel(inputElt);
        this.removeInvalidFeedbackElt(labelElt);

        inputElt.classList.remove("is-valid");
        inputElt.classList.add("is-invalid");

        let invalidFeedbackElt = document.createElement("div");
        invalidFeedbackElt.className = "invalid-feedback d-block js-invalid";
        invalidFeedbackElt.innerHTML = `
                <span class="form-error-icon badge badge-danger text-uppercase">Erreur</span> 
                <span class="form-error-message">${msg}</span>
                `
        labelElt.appendChild(invalidFeedbackElt);
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
}