import MessageFlash from "../utils/messageFlash";

export default class ShowNote {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.cardElts = document.querySelectorAll(".card");
        this.modalForm = document.querySelector(".modal-content");
        this.newNoteBtn = document.getElementById("js-new-note");
        this.init();
    }

    init() {
        this.cardElts.forEach(cardElt => {
            cardElt.addEventListener("click", this.getNote.bind(this, cardElt));
        });

        this.newNoteBtn.addEventListener("click", this.createNote.bind(this));
    }

    createNote() {
        this.modalForm.querySelector(".cke_contents").style.height = "300px";
        this.modalForm.querySelector("form").action = "";
        this.modalForm.querySelector("#note_title").value = "";
        // this.selectOption(this.modalForm.querySelector("#note_type"), "1");
        // this.selectOption(this.modalForm.querySelector("#note_status"), "2");
        let bodyElt = document.querySelector("#cke_1_contents>iframe").contentWindow.document.querySelector("body");
        bodyElt.innerHTML = "";
    }

    getNote(cardElt) {
        this.modalForm.querySelector(".cke_contents").style.height = "300px";
        let cardTextHtml = cardElt.querySelector(".card-text").innerHTML;

        let cardId = Number(cardElt.id.replace("note-", ""));
        this.modalForm.querySelector("form").action = "/note/" + cardId + "/edit";

        let titleText = cardElt.querySelector(".card-title>a").textContent;
        this.modalForm.querySelector("#note_title").value = titleText;

        let typeValue = cardElt.querySelector(".js-noteType").getAttribute("data-value");
        this.selectOption(this.modalForm.querySelector("#note_type"), typeValue);

        let statusValue = cardElt.querySelector(".js-noteStatus").getAttribute("data-value");
        this.selectOption(this.modalForm.querySelector("#note_status"), statusValue);

        let bodyElt = document.querySelector("#cke_1_contents>iframe").contentWindow.document.querySelector("body");
        bodyElt.innerHTML = cardTextHtml;
    }

    // 
    selectOption(selectElt, value) {
        selectElt.querySelectorAll("option").forEach(option => {
            if (option.value === value) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });
    }

    test() {
        this.updatePersonBtn.addEventListener("click", function (e) {
            e.preventDefault();
            if (!validationPerson.getNbErrors()) {
                let formData = new FormData(this.personElt);
                let formToString = new URLSearchParams(formData).toString();
                this.ajaxRequest.init("POST", this.url, this.response.bind(this), true, formToString);
            } else {
                new MessageFlash("danger", "Veuillez corriger les erreurs avant de mettre à jour.");
            }
        }.bind(this));
    }

    response(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.code === 200) {
            dataJSON.msg.forEach(msg => {
                new MessageFlash(dataJSON.alert, msg);
                if (dataJSON.alert === "success") {
                    document.getElementById("js-person-updated").textContent = "(modifié le " + dataJSON.date + " par " + dataJSON.user + ")";
                }
            });
        }
    }
}