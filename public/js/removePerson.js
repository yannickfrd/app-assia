// Requête Ajax pour retirer une personne d'un groupe
class RemovePerson {

    constructor() {
        this.trPersonElts = document.querySelectorAll(".js-tr-person");
        this.inputNbPeople = document.getElementById("group_people_nbPeople");
        this.modalConfirmElt = document.getElementById("modal-confirm");
        this.trPersonElt = null;
        this.init();
    }

    init() {
        this.trPersonElts.forEach(trPersonElt => {
            let aRemoveElt = trPersonElt.querySelector("a.js-remove");
            aRemoveElt.addEventListener("click", function (e) {
                e.preventDefault();
                this.modalConfirmElt.addEventListener("click", this.removePerson.bind(this, aRemoveElt, trPersonElt), {
                    once: true
                });
            }.bind(this));
        });
    }

    // Envoie la requête Ajax après confirmation de l'action
    removePerson(aRemoveElt, trPersonElt) {
        this.trPersonElt = trPersonElt;
        ajaxRequest.init("GET", aRemoveElt.href, this.response.bind(this), true), {
            once: true
        };
    }

    // Récupère les données envoyés par le serveur
    response(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.code === 200) {
            this.deleteTr(this.trPersonElt);
            this.inputNbPeople.value = dataJSON.data;
            new MessageFlash("warning", dataJSON.msg);
        } else {
            new MessageFlash("danger", dataJSON.msg);
        }
    }

    // Supprime la ligne correspondant à la personne dans le tableau
    deleteTr() {
        this.trPersonElt.remove();
    }
}

let removePerson = new RemovePerson();