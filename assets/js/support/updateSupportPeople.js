import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";

// Retire une personne du suivi du social du groupe
export default class UpdateSupportPeople {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.trElts = document.querySelectorAll(".js-tr-support_pers");
        this.modalConfirmElt = document.getElementById("modal-confirm");
        this.addPeopleBtElt = document.getElementById("add-people");
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
        this.addPeopleBtElt.addEventListener("click", () => {
            this.loader.on();
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

    // Récupère les données envoyés par le serveur
    response(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.code === 200) {
            this.deleteTr(this.trElt);
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