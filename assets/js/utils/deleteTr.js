// Retirer une ligne d'un tableau
export default class DeleteTr {

    constructor(tableId) {
        this.trElts = document.querySelectorAll("#" + tableId + ">tbody>tr");
        this.init();
    }

    init() {
        this.trElts.forEach(trElt => {
            let btnRemoveElt = trElt.querySelector("button.js-remove");
            btnRemoveElt.addEventListener("click", e => {
                e.preventDefault();
                trElt.remove();
            });
        });
    }
}