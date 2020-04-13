import MessageFlash from "../utils/messageFlash";

//
export default class AddPerson {

    constructor() {
        this.addPersonElts = document.querySelectorAll(".js-add-person");
        this.aCreatePersonElt = document.querySelector(".js-create-person");
        this.formModal = document.querySelector(".modal-content form");
        this.btnConfirm = document.getElementById("js-btn-confirm");
        this.trPersonElt = null;
        this.init();
    }

    init() {
        this.addPersonElts.forEach(person => {
            person.addEventListener("click", e => {
                e.preventDefault();
                let href = person.href;
                this.formModal.action = href;
            });
        });

        if (this.aCreatePersonElt) {
            this.aCreatePersonElt.addEventListener("click", this.setParams.bind(this));
        }
    }

    // Crée les paramètres en GET dans l'URL
    setParams() {
        let params = "";
        let inputsElt = document.querySelectorAll("input");
        inputsElt.forEach(input => {
            if (input.id != "search") {
                let key = input.id;
                params += key + "=" + input.value + "&";
            }
        });
        this.aCreatePersonElt.href = this.aCreatePersonElt.href + "?" + params;
    }

    // Envoie la requête Ajax après confirmation de l'action
    removePerson(aRemoveElt, trPersonElt) {
        this.trPersonElt = trPersonElt;
        ajaxRequest.init("GET", aRemoveElt.href, this.response.bind(this), true), {
            once: true
        };
    }

    response(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.code === 200) {
            this.deleteTr(this.trPersonElt);
            new MessageFlash("warning", dataJSON.result);
        } else {
            new MessageFlash("danger", dataJSON.result);
        }
    }

    // Supprime la ligne correspondant à la personne dans le tableau
    deleteTr() {
        this.trPersonElt.remove();
    }
}