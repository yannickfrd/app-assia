//
class AddPerson {

    constructor() {
        this.addPersonElts = document.querySelectorAll(".js-add-person");
        this.formModal = document.querySelector(".modal-content form");
        this.btnConfirm = document.getElementById("js-btn-confirm");
        this.trPersonElt = null;
        this.init();
    }

    init() {
        this.addPersonElts.forEach(person => {
            person.addEventListener("click", function (e) {
                e.preventDefault();
                let href = person.href;
                // console.log(href);
                this.formModal.action = href;
                // this.btnConfirm.href = href;
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

let addPerson = new AddPerson();