//
export default class ValidationInput {

    constructor() {}

    // Met le champ en valide 
    valid(field, label, input) {
        if (input.classList.contains("is-invalid")) {
            input.classList.replace("is-invalid", "is-valid");
            document.querySelector(".js-invalid-" + field).remove();
        } else {
            input.classList.add("is-valid");
        }
        label.pseudoStyle("after", "display", "");
    }

    // Met le champ en invalide et met un message d'erreur
    invalid(field, label, input, msg) {
        if (document.querySelector("label>span.js-invalid-" + field)) {
            document.querySelector("span.js-invalid-" + field).remove();
        }
        if (!input.classList.contains("is-invalid")) {
            input.classList.add("is-invalid");
        }
        let invalidFeedbackElt = document.createElement("span");
        invalidFeedbackElt.className = "invalid-feedback d-block js-invalid js-invalid-" + field;
        invalidFeedbackElt.innerHTML = `
                <span class="form-error-icon badge badge-danger text-uppercase">Erreur</span> 
                <span class="form-error-message">${msg}</span>
                `
        label.appendChild(invalidFeedbackElt);
        label.pseudoStyle("after", "display", "none");
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
    var _head = document.head || document.getElementsByTagName('head')[0];
    var _sheet = document.getElementById(_sheetId) || document.createElement('style');
    _sheet.id = _sheetId;
    var className = "pseudoStyle" + UID.getNew();

    _this.className += " " + className;

    _sheet.innerHTML += "\n." + className + ":" + element + "{" + prop + ":" + value + "}";
    _head.appendChild(_sheet);
    return this;
};