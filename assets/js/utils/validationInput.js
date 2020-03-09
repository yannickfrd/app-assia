// Message de validité d'un champ de formulaire
export default class ValidationInput {

    // Met le champ en valide 
    valid(inputElt) {

        let labelElt = this.getlabel(inputElt);

        this.removeInvalidFeedbackElt(labelElt);

        inputElt.classList.remove("is-invalid");
        inputElt.classList.add("is-valid");

        labelElt.pseudoStyle("after", "display", "");
    }

    // Met le champ en invalide et indique un message d'erreur
    invalid(inputElt, msg) {

        let labelElt = this.getlabel(inputElt);

        this.removeInvalidFeedbackElt(labelElt);

        inputElt.classList.remove("is-valid");
        inputElt.classList.add("is-invalid");

        let invalidFeedbackElt = document.createElement("span");
        invalidFeedbackElt.className = "invalid-feedback d-block js-invalid";
        invalidFeedbackElt.innerHTML = `
                <span class="form-error-icon badge badge-danger text-uppercase">Erreur</span> 
                <span class="form-error-message">${msg}</span>
                `
        labelElt.appendChild(invalidFeedbackElt);
        labelElt.pseudoStyle("after", "display", "none");
    }

    getlabel(inputElt) {
        let labelElt = inputElt.parentNode.parentNode.querySelector("label");
        if (labelElt) {
            return labelElt;
        }
        return inputElt.parentNode;
    }

    removeInvalidFeedbackElt(labelElt) {
        let invalidFeedbackElt = labelElt.querySelector("span.js-invalid");
        if (invalidFeedbackElt) {
            invalidFeedbackElt.remove();
        }
    }
}

var UID = {
    _current: 0,
    getNew: function () {
        this._current++;
        return this._current;
    }
};

HTMLElement.prototype.pseudoStyle = function (element, prop, value) {
    var _this = this;
    var _sheetId = "pseudoStyles";
    var _head = document.head || document.getElementsByTagName("head")[0];
    var _sheet = document.getElementById(_sheetId) || document.createElement("style");
    _sheet.id = _sheetId;
    var className = "pseudoStyle" + UID.getNew();

    _this.className += " " + className;

    _sheet.innerHTML += "\n." + className + ":" + element + "{" + prop + ":" + value + "}";
    _head.appendChild(_sheet);
    return this;
};