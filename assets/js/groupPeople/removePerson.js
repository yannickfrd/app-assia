import MessageFlash from "../utils/messageFlash";

// Requête Ajax pour retirer une personne d'un groupe
export default class RemovePerson {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.trPersonElts = document.querySelectorAll(".js-tr-person");
        this.inputNbPeople = document.getElementById("group_people_nbPeople");
        this.modalConfirmElt = document.getElementById("modal-confirm");
        this.trPersonElt = null;
        this.init();
    }

    init() {
        this.trPersonElts.forEach(trPersonElt => {
            let btnRemoveElt = trPersonElt.querySelector("button.js-remove");
            btnRemoveElt.addEventListener("click", function (e) {
                e.preventDefault();
                this.modalConfirmElt.addEventListener("click", this.removePerson.bind(this, btnRemoveElt, trPersonElt), {
                    once: true
                });
            }.bind(this));
        });
    }

    // Envoie la requête Ajax après confirmation de l'action
    removePerson(btnRemoveElt, trPersonElt) {
        this.trPersonElt = trPersonElt;
        this.ajaxRequest.init("GET", btnRemoveElt.getAttribute("data-url"), this.response.bind(this), true), {
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