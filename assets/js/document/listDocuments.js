import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";

export default class ListDocuments {

    constructor() {
        this.modalDocumentElt = document.getElementById("modal-document");
        this.formDocumentElt = this.modalDocumentElt.querySelector("form[name=document]");
        this.documentNameInput = this.modalDocumentElt.querySelector("#document_name");
        this.documentTypeInput = this.modalDocumentElt.querySelector("#document_type");
        this.documentContentInput = this.modalDocumentElt.querySelector("#document_content");
        this.documentFileInput = this.modalDocumentElt.querySelector("#document_file");
        this.documentFileLabelElt = this.modalDocumentElt.querySelector(".custom-file-label");
        this.btnSaveElt = this.modalDocumentElt.querySelector("#js-btn-save");
        this.btnDeleteElt = this.modalDocumentElt.querySelector("#modal-btn-delete");

        this.modalConfirmElt = document.getElementById("modal-confirm");

        this.themeColor = document.getElementById("header").getAttribute("data-color");
        this.countDocumentsElt = document.getElementById("count-documents");
        this.supportId = document.getElementById("container-documents").getAttribute("data-support");

        this.loader = new Loader("#modal-document");

        this.init();
    }

    init() {
        document.getElementById("js-new-document").addEventListener("click", this.newDocument.bind(this));

        this.documentFileInput.addEventListener("input", this.checkFile.bind(this));

        document.querySelectorAll(".js-document").forEach(documentElt => {
            documentElt.addEventListener("click", this.getDocument.bind(this, documentElt));
            let btnElt = documentElt.querySelector("button.js-delete");
            btnElt.addEventListener("click", function (e) {
                this.modalConfirmElt.setAttribute("data-url", btnElt.getAttribute("data-url"));
            }.bind(this));
        });

        this.btnSaveElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.saveDocument();
        }.bind(this));

        document.getElementById("js-btn-cancel").addEventListener("click", function (e) {
            e.preventDefault();
        }.bind(this));

        this.btnDeleteElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.deleteDocument(this.btnDeleteElt.href);
        }.bind(this));

        this.modalConfirmElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.ajaxRequest(this.modalConfirmElt.getAttribute("data-url"), "GET", null, false, false);
        }.bind(this));
    }

    // Affiche un formulaire modal vierge
    newDocument() {
        this.modalDocumentElt.querySelector("form").action = "/support/" + this.supportId + "/document/new";
        this.documentNameInput.value = "";
        this.selectOption(this.documentTypeInput, null)
        this.documentContentInput.value = "";
        this.modalDocumentElt.querySelector(".js-document-block-file").classList.remove("d-none");
        this.documentFileInput.value = null;
        this.documentFileLabelElt.textContent = "Choisir un fichier...";
        this.documentFileLabelElt.classList.remove("small");
        this.btnDeleteElt.classList.replace("d-block", "d-none");
        this.btnSaveElt.setAttribute("data-action", "new");
        this.btnSaveElt.textContent = "Enregistrer";
    }

    // Donne le document sélectionné dans le formulaire modal
    getDocument(documentElt) {
        this.documentElt = documentElt;
        this.contentDocumentElt = documentElt.querySelector(".document-content");

        this.documentId = Number(documentElt.id.replace("document-", ""));
        this.modalDocumentElt.querySelector("form").action = "/document/" + this.documentId + "/edit";

        this.nameDocumentElt = documentElt.querySelector(".js-document-name");
        this.documentNameInput.value = this.nameDocumentElt.textContent;

        let typeValue = documentElt.querySelector(".js-document-type").getAttribute("data-value");
        this.selectOption(this.documentTypeInput, typeValue);

        this.contentDocumentElt = documentElt.querySelector(".js-document-content");
        this.documentContentInput.value = this.contentDocumentElt.textContent;

        this.modalDocumentElt.querySelector(".js-document-block-file").classList.add("d-none");

        this.btnDeleteElt.classList.replace("d-none", "d-block");
        this.btnDeleteElt.href = "/document/" + this.documentId + "/delete";

        this.btnSaveElt.setAttribute("data-action", "edit");
        this.btnSaveElt.textContent = "Mettre à jour";
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

    // Vérifie le fichier choisi
    checkFile() {
        let error = false;
        let validExtensions = ["doc", "docx", "jpg", "pdf", "png", "rar", "xls", "xlsx", "zip"];
        let extensionFile = this.documentFileInput.value.split(".").pop().toLowerCase();
        // Vérifie si l'extension du fichier est valide
        if ((validExtensions.indexOf(extensionFile) === -1)) {
            error = true;
            new MessageFlash("danger", "Le format du fichier n'est pas valide (" + extensionFile + "). Formats acceptés : doc, docx, jpg, pdf, png, rar, xls, xlsx, zip.");
        }

        let sizeFile = Math.round((this.documentFileInput.files[0].size / 1024 / 1024) * 10) / 10;
        // Vérifie si le fichier est supérieur à 5 Mo
        if (sizeFile > 5) {
            error = true;
            new MessageFlash("danger", "Le fichier est trop volumineux (" + sizeFile + " Mo). Maximum : 5 Mo.");
        }
        // Si le fichier est valide, affiche le nom
        if (error === false) {
            let fileName = this.documentFileInput.value.split("\\").pop();
            this.documentFileLabelElt.textContent = fileName;
            this.documentFileLabelElt.classList.add("small");
            if (!this.documentNameInput.value) {
                this.documentNameInput.value = fileName.split(".").slice(0, -1).join(".");
            }
            // Sinon, retire le fichier de l'input
        } else {
            this.documentFileInput.value = null;
            this.documentFileLabelElt.textContent = "Choisir un fichier...";
            this.documentFileLabelElt.classList.remove("small");
        }
    }

    // Enregistre le document
    saveDocument() {
        let error = false;

        if (!this.documentNameInput.value) {
            error = true;
            new MessageFlash("danger", "Le nom du document est vide.");
        }

        if (!this.getOption((this.documentTypeInput))) {
            error = true;
            new MessageFlash("danger", "Le type de document n'est pas renseigné.");
        }

        if (this.btnSaveElt.getAttribute("data-action") === "new" && !this.documentFileInput.value) {
            error = true;
            new MessageFlash("danger", "Il n'y a pas de fichier choisi.");
        }

        if (error === false) {
            let formData = new FormData(this.formDocumentElt);
            formData.append("file", $("input[type=file]")[0].files[0]);
            this.ajaxRequest(this.formDocumentElt.getAttribute("action"), "POST", formData, false, false);
        }
    }

    // Envoie une requête ajax pour supprimer le document
    deleteDocument(url) {
        if (window.confirm("Voulez-vous vraiment supprimer ce document ?")) {
            this.ajaxRequest(url, "GET", null, false, false);
        }
    }

    // Requête Ajax
    ajaxRequest(url, type, data, processData, contentType) {
        this.loader.on(true);
        $.ajax({
            url: url,
            type: type,
            data: data,
            processData: processData,
            contentType: contentType,
            success: function (response) {
                this.responseAjax(response);
            }.bind(this),
            error: function (jqXHR, textStatus, errorMessage) {
                if (errorMessage === "Forbidden") {
                    return new MessageFlash("danger", "Vous n'avez pas les droits pour effectuer cette action.");
                }
                return new MessageFlash("danger", "Une erreur s'est produite : " + errorMessage);
            }.bind(this)
        });
    }

    // Réponse du serveur
    responseAjax(data) {
        if (data.code === 200) {
            switch (data.action) {
                case "create":
                    this.createDocument(data.data);
                    break;
                case "update":
                    this.updateDocument(data.data);
                    break;
                case "delete":
                    document.getElementById("document-" + this.documentId).remove();
                    this.countDocumentsElt.textContent = parseInt(this.countDocumentsElt.textContent) - 1;
                    break;
            }
        }
        new MessageFlash(data.alert, data.msg);
        this.loader.off();
    }

    // Crée la ligne du nouveau document dans le tableau
    createDocument(data) {
        let documentElt = document.createElement("tr");
        documentElt.id = "document-" + data.documentId;
        documentElt.className = "js-document";

        documentElt.innerHTML = this.getPrototypeDocument(data);

        let containerDocumentsElt = document.getElementById("container-documents");
        containerDocumentsElt.insertBefore(documentElt, containerDocumentsElt.firstChild);
        this.countDocumentsElt.textContent = parseInt(this.countDocumentsElt.textContent) + 1;
        documentElt.addEventListener("click", this.getDocument.bind(this, documentElt));
        let btnElt = documentElt.querySelector("button.js-delete");
        btnElt.addEventListener("click", function (e) {
            this.modalConfirmElt.setAttribute("data-url", btnElt.getAttribute("data-url"));
        }.bind(this));
    }

    // Met à jour la ligne du tableau correspondant au document
    updateDocument(data) {
        this.nameDocumentElt.textContent = this.documentNameInput.value;
        let documentTypeInput = this.documentElt.querySelector(".js-document-type");
        documentTypeInput.textContent = data.typeList;
        documentTypeInput.setAttribute("data-value", this.getOption(this.documentTypeInput));
        this.documentElt.querySelector(".js-document-content").textContent = this.documentContentInput.value;
    }

    getPrototypeDocument(data) {
        let size = Math.floor(data.size / 1000) + " Ko";

        return `<th scope="row" class="align-middle text-center">
                    <a href="/uploads/documents/${data.path}" target="_blank" class="btn btn-${this.themeColor} btn-sm shadow my-1" title="Télécharger le document"><span class="fas fa-file-download"></span></a>
                </th>
                    <td class="js-document-name" data-toggle="modal" data-target="#modal-document">${this.documentNameInput.value}</td>
                    <td class="js-document-type" data-toggle="modal" data-target="#modal-document" data-value="${this.getOption(this.documentTypeInput)}">${data.typeList}</td>
                    <td class="js-document-content" data-toggle="modal" data-target="#modal-document">${this.documentContentInput.value}</td>
                    <td class="js-document-size text-right" data-toggle="modal" data-target="#modal-document">${size}</td>
                    <td class="js-document-createdAt" data-toggle="modal" data-target="#modal-document">${data.createdAt}</td>
                    <td class="align-middle text-center">
                        <button data-url="/document/${data.documentId}/delete" class="js-delete btn btn-danger btn-sm shadow my-1" title="Supprimer le document" data-toggle="modal" data-target="#modal-document"><span class="fas fa-trash-alt"></span></button>
                </td>`
    }
}