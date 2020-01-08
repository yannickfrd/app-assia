import MessageFlash from "../utils/messageFlash";
import DateFormat from "../utils/dateFormat";

export default class Calendar {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.dayElts = document.querySelectorAll(".calendar-day-block");
        this.rdvElts = document.querySelectorAll(".js-rdv");
        this.modalForm = document.querySelector(".modal-content");
        this.rdvContentElt = document.getElementById("rdv_content");
        this.newRdvBtn = document.getElementById("js-new-rdv");
        this.formRdvElt = document.querySelector("form[name=rdv]");
        this.rdvStartInput = document.getElementById("rdv_start");
        this.rdvEndInput = document.getElementById("rdv_end");

        this.dateInput = document.getElementById("date");
        this.startInput = document.getElementById("start");
        this.endInput = document.getElementById("end");

        this.btnSaveElt = document.getElementById("js-btn-save");
        this.btnCancelElt = document.getElementById("js-btn-cancel");
        this.btnDeleteElt = document.getElementById("modal-btn-delete");
        this.loaderElt = document.getElementById("loader");
        this.themeColor = this.loaderElt.getAttribute("data-value");
        this.supportElt = document.getElementById("support");
        // this.supportId;
        this.init();
    }

    init() {
        this.newRdvBtn.addEventListener("click", this.resetData.bind(this));

        this.dayElts.forEach(dayElt => {
            this.hideRdvElts(dayElt);
            dayElt.addEventListener("click", function () {
                this.resetData();
                this.dateInput.value = dayElt.id;
                this.modalForm.querySelector("#rdv_start").value = dayElt.id + "T00:00";
                this.modalForm.querySelector("#rdv_end").value = dayElt.id + "T00:00";
            }.bind(this));
        });

        this.rdvElts.forEach(rdvElt => {
            rdvElt.addEventListener("click", function () {
                this.resetData();
                this.requestGetRdv(rdvElt);
            }.bind(this));
        });

        this.dateInput.addEventListener("focusout", this.checkDate.bind(this));
        this.startInput.addEventListener("input", this.checkStart.bind(this));
        this.endInput.addEventListener("focusout", this.checkEnd.bind(this));

        this.btnSaveElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.requestSaveRdv();
        }.bind(this));

        this.btnCancelElt.addEventListener("click", function (e) {
            e.preventDefault();
        }.bind(this));

        this.btnDeleteElt.addEventListener("click", function (e) {
            e.preventDefault();
            this.requestDeleteRdv();
        }.bind(this));
    }

    resetData() {
        if (this.supportElt) {
            this.supportId = this.supportElt.getAttribute("data");
            this.modalForm.querySelector("form").action = "/support/" + this.supportId + "/rdv/new";
        } else {
            this.modalForm.querySelector("form").action = "/rdv/new";
        }

        this.modalForm.querySelector("#rdv_title").value = "";

        let dateFormat = new DateFormat();
        this.dateInput.value = dateFormat.getDateNow();
        this.startInput.value = dateFormat.getHour();
        let end = parseInt(this.startInput.value.substr(0, 2)) + 1;
        this.endInput.value = end + ":00";

        this.rdvStartInput.value = "";
        this.rdvEndInput.value = "";

        // this.modalForm.querySelector("#rdv_status").value = 0;
        this.modalForm.querySelector("#rdv_content").value = "";
        this.btnDeleteElt.classList.replace("d-block", "d-none");
    }

    // Vérifie si la date est valide
    checkDate() {
        this.updateDatetimes();
    }

    // Vérifie si l'heure de début est valide
    checkStart() {
        if (isNaN(this.startInput.value)) {
            let endHour = parseInt(this.startInput.value.substr(0, 2)) + 1;
            if (endHour < 10) {
                endHour = "0" + endHour;
            }
            this.endInput.value = endHour + ":" + this.startInput.value.substr(3, 2);
            this.updateDatetimes();
        }
    }

    // Vérifie si l'heure de fin est valide
    checkEnd() {
        this.updateDatetimes();
    }

    // Met à jour les dates de début et de fin
    updateDatetimes() {
        if (isNaN(this.dateInput.value) && isNaN(this.startInput.value)) {
            this.rdvStartInput.value = this.dateInput.value + "T" + this.startInput.value;
        }
        if (isNaN(this.dateInput.value) && isNaN(this.endInput.value)) {
            this.rdvEndInput.value = this.dateInput.value + "T" + this.endInput.value;
        }
    }

    requestGetRdv(rdvElt) {

        this.loaderElt.classList.remove("d-none");

        this.rdvElt = rdvElt;
        this.rdvId = Number(this.rdvElt.id.replace("rdv-", ""));

        this.ajaxRequest.init("GET", "/rdv/" + this.rdvId + "/get", this.responseAjax.bind(this), true);
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

    requestSaveRdv() {
        if (this.modalForm.querySelector("#rdv_title").value != "") {
            let formData = new FormData(this.formRdvElt);
            let formToString = new URLSearchParams(formData).toString();
            this.animateLoader();
            console.log(this.formRdvElt.getAttribute("action"));
            this.ajaxRequest.init("POST", this.formRdvElt.getAttribute("action"), this.responseAjax.bind(this), true, formToString);
        } else {
            // $("#modal-block").modal("hide");
            new MessageFlash("danger", "La rdv est vide.");
        }
    }

    requestDeleteRdv() {
        this.animateLoader();
        this.ajaxRequest.init("POST", this.btnDeleteElt.href, this.responseAjax.bind(this), true, null);
    }

    animateLoader() {
        $("#modal-block").modal("hide");
        this.loaderElt.classList.remove("d-none");
    }

    responseAjax(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.code === 200) {
            if (dataJSON.action === "show") {
                this.showRdv(dataJSON.data);
            }
            if (dataJSON.action === "create") {
                this.createRdv(dataJSON.data);
            }
            if (dataJSON.action === "update") {
                this.updateRdv(dataJSON.data);
            }
            if (dataJSON.action === "delete") {
                this.deleteRdv(dataJSON.data);
            }
        }
        if (dataJSON.msg) {
            new MessageFlash(dataJSON.alert, dataJSON.msg);
        }
        this.loaderElt.classList.add("d-none");
    }

    showRdv(data) {
        this.modalForm.querySelector("form").action = "/rdv/" + this.rdvId + "/edit";
        this.modalForm.querySelector("#rdv_title").value = data.title;
        this.modalForm.querySelector("#js-support-fullname").textContent = data.supportFullname;
        this.rdvStartInput.value = data.start;
        this.rdvEndInput.value = data.end;

        this.dateInput.value = data.start.substr(0, 10);
        this.startInput.value = data.start.substr(11, 5);
        this.endInput.value = data.end.substr(11, 5);

        this.modalForm.querySelector("#rdv_location").value = data.location;
        // this.modalForm.querySelector("#rdv_status").value = data.status;
        this.modalForm.querySelector("#rdv_content").value = data.content;

        this.btnDeleteElt.classList.replace("d-none", "d-block");
        this.btnDeleteElt.href = "/rdv/" + this.rdvId + "/delete";
    }

    createRdv(data) {
        let rdvElt = document.createElement("div");
        rdvElt.className = "calendar-event bg-" + this.themeColor + " text-light js-rdv";
        rdvElt.id = "rdv-" + data.rdvId;
        rdvElt.setAttribute("data-toggle", "modal");
        rdvElt.setAttribute("data-target", "#modal-block");
        rdvElt.setAttribute("title", "Voir le rendez-vous");

        let title = this.modalForm.querySelector("#rdv_title").value;

        rdvElt.innerHTML =
            ` <span class="rdv-start">${data.start}</span> 
                <span class="rdv-title">${title}</span> `

        let dayElt = document.getElementById(data.day);
        dayElt.insertBefore(rdvElt, dayElt.lastChild);

        this.sortDayBlock(dayElt);
        this.hideRdvElts(dayElt);

        rdvElt.addEventListener("click", this.requestGetRdv.bind(this, rdvElt));
    }

    updateRdv(data) {
        this.rdvElt.querySelector(".rdv-start").textContent = data.start;
        this.rdvElt.querySelector(".rdv-title").textContent = this.modalForm.querySelector("#rdv_title").value;
    }

    deleteRdv() {
        let rdvElt = document.getElementById("rdv-" + this.rdvId);
        let dayElt = rdvElt.parentNode;
        rdvElt.remove();
        this.hideRdvElts(dayElt);
    }

    // Tri les événements du jour
    sortDayBlock(dayElt) {

        let rdvArr = [];
        dayElt.querySelectorAll(".calendar-event").forEach(rdvElt => {
            rdvArr.push(rdvElt);
        });

        rdvArr.sort((a, b) => a.innerText > b.innerText ? 1 : -1)
            .map(node => dayElt.appendChild(node));
    }

    hideRdvElts(dayElt) {

        let rdvElts = dayElt.querySelectorAll(".calendar-event");

        let othersEventsElt = dayElt.querySelector(".calendar-others-events")
        if (othersEventsElt) {
            othersEventsElt.remove();
        }

        let maxHeight = (dayElt.clientHeight - 24) / 21.2;

        dayElt.querySelectorAll("a").forEach(divElt => {
            divElt.classList.remove("d-none");
        });

        let sumHeightdivElts = 44;
        dayElt.querySelectorAll("a").forEach(divElt => {
            var styles = window.getComputedStyle(divElt);
            sumHeightdivElts = sumHeightdivElts + divElt.clientHeight + parseFloat(styles["marginTop"]) + parseFloat(styles["marginBottom"]);
            if (sumHeightdivElts > dayElt.clientHeight && rdvElts.length > maxHeight) {
                divElt.classList.add("d-none");
            }
        });

        if (sumHeightdivElts > dayElt.clientHeight && rdvElts.length > maxHeight) {
            let divElt = document.createElement("a");
            divElt.className = "calendar-others-events bg-" + this.themeColor + " text-light font-weight-bold";
            divElt.setAttribute("data-toggle", "modal");
            divElt.setAttribute("data-target", "#modal-block");
            divElt.setAttribute("title", "Voir tous les rendez-vous");
            let spanElt = document.createElement("span");
            spanElt.textContent = (parseInt(rdvElts.length - maxHeight) + 2) + " autres...";
            divElt.appendChild(spanElt);
            dayElt.insertBefore(divElt, dayElt.lastChild);
        }
    }
}