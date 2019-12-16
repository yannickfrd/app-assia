import MessageFlash from "../utils/messageFlash";

export default class EditNote {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.noteElts = document.querySelectorAll(".js-note");
        this.modalForm = document.querySelector(".modal-content");
        this.newNoteBtn = document.getElementById("js-new-note");
        this.formNoteElt = document.querySelector("form[name=note]");
        this.btnSaveElt = document.getElementById("js-btn-save");
        this.btnCancelElt = document.getElementById("js-btn-cancel");
        this.btnDeleteElt = document.getElementById("modal-btn-delete");
        this.loaderElt = document.getElementById("loader");
        this.themeColor = this.loaderElt.getAttribute("data-value");
        this.autoSaveElt = document.getElementById("js-auto-save");
        this.countNotesElt = document.getElementById("count-notes");
        this.autoSave = false;
        this.count = 0;
        this.init();
    }

    init() {
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

    newNote() {
        this.modalForm.querySelector("form").action = "";
        this.modalForm.querySelector("#note_title").value = "";
        let bodyElt = document.querySelector("#cke_1_contents>iframe").contentWindow.document.querySelector("body");
        bodyElt.innerHTML = "";
        this.btnDeleteElt.classList.replace("d-block", "d-none");
        bodyElt.addEventListener("keydown", this.countKeyDown.bind(this));
        this.timerAutoSave();
    }

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

        let bodyElt = document.querySelector("#cke_1_contents>iframe").contentWindow.document.querySelector("body");
        bodyElt.innerHTML = this.contentNoteElt.innerHTML;

        this.btnDeleteElt.classList.replace("d-none", "d-block");
        this.btnDeleteElt.href = "/note/" + this.cardId + "/delete";

        bodyElt.addEventListener("keydown", this.countKeyDown.bind(this));
        this.timerAutoSave();
    }

    countKeyDown() {
        this.count++;
    }

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

    clearTimer() {
        this.autoSave = false;
        this.count = 0;
        clearInterval(this.countdownID);
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

    // Retourne l'option sélelectionnée
    getOption(selectElt) {
        let optionValue;
        selectElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                optionValue = option.value;
            }
        });
        return optionValue;
    }

    saveNote() {
        if (CKEDITOR.instances.note_content.getData() != "") {
            CKEDITOR.instances.note_content.updateElement();
            let formData = new FormData(this.formNoteElt);
            let formToString = new URLSearchParams(formData).toString();
            this.animateLoader();
            this.ajaxRequest.init("POST", this.formNoteElt.getAttribute("action"), this.responseAjax.bind(this), true, formToString);
        } else {
            $("#modal-block").modal("hide");
            new MessageFlash("danger", "La note est vide.");
        }
    }

    deleteNote() {
        this.animateLoader();
        this.ajaxRequest.init("POST", this.btnDeleteElt.href, this.responseAjax.bind(this), true, null);
    }

    animateLoader() {
        if (this.autoSave) {
            this.autoSave === false;
        } else {
            $("#modal-block").modal("hide");
            this.loaderElt.classList.remove("d-none");
        }
    }

    responseAjax(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.code === 200) {
            if (dataJSON.action === "create") {
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
        this.loaderElt.classList.add("d-none");

        if (!this.autoSave) {
            new MessageFlash(dataJSON.alert, dataJSON.msg);
        }
    }

    createNote(data) {
        let note = document.createElement("div");
        note.id = "note-" + data.noteId;
        this.modalForm.querySelector("form").action = "/note/" + data.noteId + "/edit";
        this.btnDeleteElt.classList.replace("d-none", "d-block");
        let title = this.modalForm.querySelector("#note_title").value;
        let content = CKEDITOR.instances.note_content.getData();

        note.className = "col-sm-12 col-lg-6 mb-4 js-note";
        note.innerHTML =
            `<div class="card h-100 shadow">
                <div class="card-header">
                    <h5 class="card-title"><a class="text-${this.themeColor}" href="/note/${data.noteId}/edit">${title}</a></h5>
                    <span class="js-note-type" data-value="1">${data.type}</span>
                    <span class="js-note-status" data-value="1">(${data.status})</span>
                    <span class="small text-secondary">${data.editInfo}</span>
                    <span class="small text-secondary js-note-updated"></span>
                </div>
                <div class="card-body note-content cursor-pointer" data-toggle="modal" data-target="#modal-block" data-placement="bottom" title="Modifier la note">
                    <div class="card-text">${content}</div>
                    <span class="note-fadeout"></span>
                </div>
            </div>`

        let containerNotesElt = document.getElementById("container-notes");
        containerNotesElt.insertBefore(note, containerNotesElt.firstChild);
        this.countNotesElt.textContent = parseInt(this.countNotesElt.textContent) + 1;
        this.getNote(containerNotesElt.firstChild);
    }

    updateNote(data) {
        this.titleNoteElt.textContent = this.modalForm.querySelector("#note_title").value;
        this.contentNoteElt.innerHTML = CKEDITOR.instances.note_content.getData();

        let noteTypeElt = this.noteElt.querySelector(".js-note-type");
        noteTypeElt.textContent = data.type;

        noteTypeElt.setAttribute("data-value", this.getOption(this.modalForm.querySelector("#note_type")));

        let noteStatusElt = this.noteElt.querySelector(".js-note-status");
        noteStatusElt.textContent = "(" + data.status + ")";
        noteStatusElt.setAttribute("data-value", this.getOption(this.modalForm.querySelector("#note_status")));

        this.noteElt.querySelector(".js-note-updated").textContent = data.editInfo;
    }
}