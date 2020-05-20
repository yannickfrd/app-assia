import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";

export default class SupportContributions {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.modalContributionElt = document.getElementById("modal-contribution");
        this.formContributionElt = this.modalContributionElt.querySelector("form[name=contribution]");
        this.contributionTypeInput = this.modalContributionElt.querySelector("#contribution_type");
        this.btnSaveElt = this.modalContributionElt.querySelector("#js-btn-save");
        this.btnDeleteElt = this.modalContributionElt.querySelector("#modal-btn-delete");

        // this.modalConfirmElt = document.getElementById("modal-confirm");

        this.themeColor = document.getElementById("header").getAttribute("data-color");
        this.countContributionsElt = document.getElementById("count-contributions");
        this.supportId = document.getElementById("support").getAttribute("data-support");

        this.loader = new Loader("#modal-contribution");

        this.init();
    }

    init() {
        document.getElementById("js-new-contribution").addEventListener("click", this.newContribution.bind(this));

        document.querySelectorAll(".js-contribution").forEach(contributionElt => {
            let btnElt = contributionElt.querySelector("button.js-get");
            btnElt.addEventListener("click", this.getContribution.bind(this, btnElt.getAttribute("data-url")));
        });

        this.btnSaveElt.addEventListener("click", e => {
            e.preventDefault();
            this.saveContribution();
        });

        document.getElementById("js-btn-cancel").addEventListener("click", e => {
            e.preventDefault();
        });

        this.btnDeleteElt.addEventListener("click", e => {
            e.preventDefault();
            this.deleteContribution(this.btnDeleteElt.href);
        });

        // this.modalConfirmElt.addEventListener("click", e => {
        //     e.preventDefault();
        //     this.ajaxRequest(this.modalConfirmElt.getAttribute("data-url"), "GET", null, false, false);
        // });
    }

    // Affiche un formulaire modal vierge
    newContribution() {
        this.modalContributionElt.querySelector("form").action = "/support/" + this.supportId + "/contribution/new";
        // this.selectOption(this.contributionTypeInput, null)
        this.btnDeleteElt.classList.replace("d-block", "d-none");
        this.btnSaveElt.setAttribute("data-action", "new");
        this.btnSaveElt.textContent = "Enregistrer";
    }

    // Requête pour obtenir le RDV sélectionné dans le formulaire modal
    getContribution(url) {

        // this.contributionElt = contributionElt;
        // this.contentContributionElt = contributionElt.querySelector(".contribution-content");

        // this.contributionId = Number(contributionElt.id.replace("contribution-", ""));
        // this.modalContributionElt.querySelector("form").action = "/contribution/" + this.contributionId + "/edit";

        // let typeValue = contributionElt.querySelector(".js-contribution-type").getAttribute("data-value");
        // this.selectOption(this.contributionTypeInput, typeValue);

        this.btnDeleteElt.classList.replace("d-none", "d-block");
        this.btnDeleteElt.href = "/contribution/" + this.contributionId + "/delete";

        this.btnSaveElt.setAttribute("data-action", "edit");
        this.btnSaveElt.textContent = "Mettre à jour";

        this.loader.on();

        this.ajaxRequest.init("GET", url, this.responseAjax.bind(this), true);
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

    // Enregistre la redevance
    saveContribution() {
        let error = false;

        // if (!this.contributionNameInput.value) {
        //     error = true;
        //     new MessageFlash("danger", "Le nom du contribution est vide.");
        // }

        if (!this.getOption((this.contributionTypeInput))) {
            error = true;
            new MessageFlash("danger", "Le type de contribution n'est pas renseigné.");
        }

        if (error === false) {
            let formData = new FormData(this.formContributionElt);
            this.ajaxRequest(this.formContributionElt.getAttribute("action"), "POST", formData, false, false);
        }
    }



    // Envoie une requête ajax pour supprimer la redevance
    deleteContribution(url) {
        if (window.confirm("Voulez-vous vraiment supprimer cette redevance ?")) {
            this.ajaxRequest(url, "GET", null, false, false);
        }
    }

    // Réponse du serveur
    responseAjax(response) {
        let data = JSON.parse(response);

        if (data.code === 200) {
            switch (data.action) {
                case "show":
                    this.showContribution(data.data);
                    break;
                case "create":
                    this.createContribution(data.data);
                    break;
                case "update":
                    this.updateContribution(data.data);
                    break;
                case "delete":
                    document.getElementById("contribution-" + this.contributionId).remove();
                    this.countContributionsElt.textContent = parseInt(this.countContributionsElt.textContent) - 1;
                    break;
            }
        }
        new MessageFlash(data.alert, data.msg);
        this.loader.off();
    }

    // Donne la redevance sélectionnée dans le formulaire modal
    showContribution(data) {
        console.log(data.contribution);
    }

    // Crée la ligne de la nouvelle redevance dans le tableau
    createContribution(data) {
        let contributionElt = document.createElement("tr");
        contributionElt.id = "contribution-" + data.contributionId;
        contributionElt.className = "js-contribution";

        contributionElt.innerHTML = this.getPrototypeContribution(data);

        let containerContributionsElt = document.getElementById("container-contributions");
        containerContributionsElt.insertBefore(contributionElt, containerContributionsElt.firstChild);
        this.countContributionsElt.textContent = parseInt(this.countContributionsElt.textContent) + 1;
        contributionElt.addEventListener("click", this.getContribution.bind(this, contributionElt));
        let btnElt = contributionElt.querySelector("button.js-delete");
        btnElt.addEventListener("click", e => {
            // this.modalConfirmElt.setAttribute("data-url", btnElt.getAttribute("data-url"));
        });
    }

    // Met à jour la ligne du tableau correspondant au contribution
    updateContribution(data) {
        let contributionTypeInput = this.contributionElt.querySelector(".js-contribution-type");
        contributionTypeInput.setAttribute("data-value", this.getOption(this.contributionTypeInput));
        this.contributionElt.querySelector(".js-contribution-content").textContent = this.contributionContentInput.value;
    }

    getPrototypeContribution(data) {
        return `<th scope="row" class="align-middle text-center">
                    <a href="/contribution/${data.contributionId}/read" class="btn btn-${this.themeColor} btn-sm shadow my-1" title="Télécharger la redevance"><span class="fas fa-file-download"></span></a>
                </th>
                    <td class="js-contribution-type" data-toggle="modal" data-target="#modal-contribution" data-value="${this.getOption(this.contributionTypeInput)}">${data.type}</td>
                    <td class="js-contribution-createdAt" data-toggle="modal" data-target="#modal-contribution">${data.createdAt}</td>
                    <td class="align-middle text-center">
                        <button data-url="/contribution/${data.contributionId}/delete" class="js-delete btn btn-danger btn-sm shadow my-1" title="Supprimer la redevance" data-toggle="modal" data-target="#modal-contribution"><span class="fas fa-trash-alt"></span></button>
                    </td>`
    }
}