// Requête Ajax pour retirer une personne d'un groupe
class RemovePerson {

    constructor() {
        this.trPersonElts = document.querySelectorAll(".js-tr-person");
        this.modalConfirmElt = document.getElementById("modal-confirm");
        this.init();
    }

    init() {

        this.trPersonElts.forEach(trPersonElt => {
            let aRemoveElt = trPersonElt.querySelector("a.js-remove");
            aRemoveElt.addEventListener("click", function (e) {
                e.preventDefault();

                this.modalConfirmElt.addEventListener("click", this.removePerson.bind(this, trPersonElt, aRemoveElt), {
                    once: true
                });
            }.bind(this));
        });
    }

    // Envoie la requête Ajax après confirmation de l'action
    removePerson(trPersonElt, aRemoveElt) {
        // let token = document.getElementById("group_people__token").value;
        ajaxRequest.init("GET", aRemoveElt.href, true);
        let response = ajaxRequest.response();
        response.addEventListener("loadend", function () {
            let responseJSON = JSON.parse(response.responseText);
            if (responseJSON.code === 200) {
                this.deleteTr(trPersonElt);
                new MessageFlash("alert-warning", responseJSON.result);
            } else {
                new MessageFlash("alert-danger", responseJSON.result);
            }
        }.bind(this), {
            once: true
        });
    }

    // Supprime la ligne correspondant à la personne dans le tableau
    deleteTr(trPersonElt) {
        trPersonElt.remove();
    }
}

let removePerson = new RemovePerson();