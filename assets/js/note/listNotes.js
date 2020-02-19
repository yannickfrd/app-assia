import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";
import DecoupledEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import language from "@ckeditor/ckeditor5-build-decoupled-document/build/translations/fr.js";

export default class ListNotes {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.noteElts = document.querySelectorAll(".js-note");
        this.modalForm = document.querySelector(".modal-content");
        this.noteContentElt = document.getElementById("note_content");
        this.editorElt = document.getElementById("editor");
        this.newNoteBtn = document.getElementById("js-new-note");
        this.formNoteElt = document.querySelector("form[name=note]");
        this.btnSaveElt = document.getElementById("js-btn-save");
        this.btnCancelElt = document.getElementById("js-btn-cancel");
        this.btnDeleteElt = document.getElementById("modal-btn-delete");
        this.loader = new Loader("#modal-block");
        this.themeColor = document.getElementById("header").getAttribute("data-color");
        this.autoSaveElt = document.getElementById("js-auto-save");
        this.countNotesElt = document.getElementById("count-notes");
        this.supportId = document.getElementById("container-notes").getAttribute("data-support");
        this.autoSave = false;
        this.count = 0;
        this.editor;
        this.init();
    }

    init() {
        this.ckEditor();

        console.log(this.modalForm);
        this.newNoteBtn.addEventListener("click", this.newNote.bind(this));

        this.noteElts.forEach(noteElt => {
            noteElt.addEventListener("click", this.getNote.bind(this, noteElt));
        });

        this.btnSaveElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.clearTimer();
            this.saveNote();
        }.bind(this));

        this.btnCancelElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.clearTimer();
        }.bind(this));

        this.btnDeleteElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.clearTimer();
            this.deleteNote();
        }.bind(this));
    }

    // Initialise CKEditor
    ckEditor() {
        DecoupledEditor
            .create(document.querySelector("#editor"), {
                toolbar: ["undo", "redo", "|", "fontFamily", "fontSize", "|", "bold", "italic", "underline", "highlight", "|", "heading", "alignment", "|", "bulletedList", "numberedList", "|", "link", "blockQuote", "|", "insertTable"],
                language: {
                    ui: "fr",
                    content: "fr"
                },
            })
            .then(editor => {
                this.editor = editor;
                const toolbarContainer = document.querySelector("#toolbar-container");
                toolbarContainer.appendChild(editor.ui.view.toolbar.element);
            })
            .catch(error => {
                console.error(error);
            });
    }

    // Affiche un formulaire modal vierge
    newNote() {
        this.modalForm.querySelector("form").action = "/support/" + this.supportId + "/note/new";
        this.modalForm.querySelector("#note_title").value = "";
        this.editor.setData("");
        this.btnDeleteElt.classList.replace("d-block", "d-none");
        this.editorElt.addEventListener("keydown", this.countKeyDown.bind(this));
        this.timerAutoSave();
    }

    // Donne la note sélectionnée dans le formulaire modal
    getNote(noteElt) {
        this.noteElt = noteElt;
        this.contentNoteElt = noteElt.querySelector(".card-text");

        this.cardId = Number(noteElt.id.replace("note-", ""));
        this.modalForm.querySelector("form").action = "/note/" + this.cardId + "/edit";

        this.titleNoteElt = noteElt.querySelector(".card-title");
        this.modalForm.querySelector("#note_title").value = this.titleNoteElt.textContent;

        let typeValue = noteElt.querySelector(".js-note-type").getAttribute("data-value");
        this.selectOption(this.modalForm.querySelector("#note_type"), typeValue);

        let statusValue = noteElt.querySelector(".js-note-status").getAttribute("data-value");
        this.selectOption(this.modalForm.querySelector("#note_status"), statusValue);

        this.editor.setData(this.contentNoteElt.innerHTML);

        this.btnDeleteElt.classList.replace("d-none", "d-block");
        this.btnDeleteElt.href = "/note/" + this.cardId + "/delete";

        this.editorElt.addEventListener("keydown", this.countKeyDown.bind(this));

        this.timerAutoSave();
    }

    // Compte le nombre de saisie dans l'éditeur de texte
    countKeyDown() {
        this.count++;
    }

    // Timer pour la sauvegarde automatique
    timerAutoSave() {
        clearInterval(this.countdownID);
        this.countdownID = setTimeout(this.timerAutoSave.bind(this), 5 * 60 * 1000);
        if (this.count > 10) {
            this.autoSave = true;
            this.count = 0;
            this.autoSaveElt.classList.add("d-block");
            setTimeout(function () {
                this.autoSaveElt.classList.remove("d-block");
            }.bind(this), 5000);
            this.saveNote();
        }
    }

    // Remet à zéro le timer
    clearTimer() {
        this.autoSave = false;
        this.count = 0;
        clearInterval(this.countdownID);
    }

    // Sélectionne une des options dans une liste select
    selectOption(selectElt, value) {
        selectElt.querySelectorAll("option").forEach(option => {
            if (option.value === value) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });
    }

    // Retourne l'option sélectionnée
    getOption(selectElt) {
        let optionValue;
        selectElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                optionValue = option.value;
            }
        });
        return optionValue;
    }

    // Envoie la requête ajax pour sauvegarder la note
    saveNote() {
        if (this.editor.getData() != "") {
            this.noteContentElt.textContent = this.editor.getData();
            let formData = new FormData(this.formNoteElt);
            let formToString = new URLSearchParams(formData).toString();

            if (!this.autoSave) {
                this.loader.on(false);
            }

            this.ajaxRequest.init("POST", this.formNoteElt.getAttribute("action"), this.responseAjax.bind(this), true, formToString);
        } else {
            new MessageFlash("danger", "La note est vide.");
        }
    }

    // Envoie la requête ajax pour supprimer la note
    deleteNote() {
        if (window.confirm("Voulez-vous vraiment supprimer cette note ?")) {
            this.loader.on(true);
            this.ajaxRequest.init("POST", this.btnDeleteElt.href, this.responseAjax.bind(this), true, null);
        }
    }

    // Réponse du serveur
    responseAjax(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.code === 200) {
            if (dataJSON.action === "create" && !this.autoSave) {
                this.createNote(dataJSON.data);
            }
            if (dataJSON.action === "update") {
                this.updateNote(dataJSON.data);
            }
            if (dataJSON.action === "delete") {
                document.getElementById("note-" + this.cardId).remove();
                this.countNotesElt.textContent = parseInt(this.countNotesElt.textContent) - 1;
            }
        }
        if (!this.autoSave) {
            new MessageFlash(dataJSON.alert, dataJSON.msg);
            this.loader.off(true);
        }
    }

    // Crée la note dans le container
    createNote(data) {
        let noteElt = document.createElement("div");
        noteElt.id = "note-" + data.noteId;
        this.modalForm.querySelector("form").action = "/note/" + data.noteId + "/edit";
        this.btnDeleteElt.classList.replace("d-none", "d-block");
        let title = this.modalForm.querySelector("#note_title").value;

        noteElt.className = "col-sm-12 col-lg-6 mb-4 js-note";
        noteElt.innerHTML =
            `<div class="card h-100 shadow">
        <div class="card-header">
        <h3 class="card-title h5 text-${this.themeColor}">${title}</h3>
        <span class="js-note-type" data-value="1">${data.type}</span>
        <span class="js-note-status" data-value="1">(${data.status})</span>
                    <span class="small text-secondary js-note-created">${data.editInfo}</span>
                    <span class="small text-secondary js-note-updated"></span>
                </div>
                <div class="card-body note-content cursor-pointer" data-toggle="modal" data-target="#modal-block" data-placement="bottom" title="Modifier la note">
                <div class="card-text">${this.editor.getData()}</div>
                <span class="note-fadeout"></span>
                </div>
                </div>`

        let containerNotesElt = document.getElementById("container-notes");
        containerNotesElt.insertBefore(noteElt, containerNotesElt.firstChild);
        this.countNotesElt.textContent = parseInt(this.countNotesElt.textContent) + 1;

        noteElt.addEventListener("click", this.getNote.bind(this, noteElt));
    }

    // Met à jour la note dans le container
    updateNote(data) {
        this.titleNoteElt.textContent = this.modalForm.querySelector("#note_title").value;
        this.contentNoteElt.innerHTML = this.editor.getData();

        let noteTypeElt = this.noteElt.querySelector(".js-note-type");
        noteTypeElt.textContent = data.type;

        noteTypeElt.setAttribute("data-value", this.getOption(this.modalForm.querySelector("#note_type")));

        let noteStatusElt = this.noteElt.querySelector(".js-note-status");
        noteStatusElt.textContent = "(" + data.status + ")";
        noteStatusElt.setAttribute("data-value", this.getOption(this.modalForm.querySelector("#note_status")));

        this.noteElt.querySelector(".js-note-updated").textContent = data.editInfo;
    }

}