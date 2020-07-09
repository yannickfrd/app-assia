import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";

// Requête Ajax pour retirer une personne d'un groupe
export default class RemovePerson {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.trElts = document.querySelectorAll(".js-tr-person");
        this.inputNbPeople = document.getElementById("group_nbPeople");
        this.modalConfirmElt = document.getElementById("modal-confirm");
        this.trElt = null;
        this.loader = new Loader();
        this.init();
    }

    init() {
        this.trElts.forEach(trElt => {
            let btnElt = trElt.querySelector("button.js-remove");
            btnElt.addEventListener("click", e => {
                e.preventDefault();
                this.modalConfirmElt.addEventListener("click", this.validate.bind(this, btnElt, trElt), {
                    once: true
                });
            });
        });
    }

    // Envoie la requête Ajax après confirmation de l'action
    validate(btnElt, trElt) {
        this.loader.on();
        this.trElt = trElt;
        this.ajaxRequest.init("GET", btnElt.getAttribute("data-url"), this.response.bind(this), true), {
            once: true
        };
    }

    // Récupère les données envoyées par le serveur
    response(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.alert === 'warning') {
            this.deleteTr(this.trElt);
            this.inputNbPeople.value = dataJSON.data;
            new MessageFlash("warning", dataJSON.msg);
        } else {
            new MessageFlash("danger", dataJSON.msg);
        }
        this.loader.off();
    }

    // Supprime la ligne correspondant à la personne dans le tableau
    deleteTr() {
        this.trElt.remove();
    }
}