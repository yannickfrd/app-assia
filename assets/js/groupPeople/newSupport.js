import AjaxRequest from "../utils/ajaxRequest";
import Loader from "../utils/loader";

// Requête Ajax pour afficher le formulaire de création d'un nouveua suivi
export default class NewSupport {

    constructor() {
        this.ajaxRequest = new AjaxRequest();
        this.btnNewSupportElt = document.getElementById("btn-new-support");
        this.containerFormElt = document.getElementById("container-form-new-support");
        this.modalElt = $("#modal-new-support");
        this.loader = new Loader();
        this.init();
    }

    init() {
        this.btnNewSupportElt.addEventListener("click", e => {
            e.preventDefault();
            if (this.loader.isInLoading() === false) {
                this.sendRequest(this.btnNewSupportElt);
            }
        });
    }

    // Envoie la requête Ajax
    sendRequest(btnElt) {
        this.loader.on();
        this.ajaxRequest.init("GET", btnElt.getAttribute("data-url"), this.response.bind(this), true), {
            once: true
        };
    }

    // Récupère les données envoyées par le serveur
    response(data) {
        this.containerFormElt.innerHTML = JSON.parse(data).data.form.content;
        this.modalElt.modal("show");
        this.loader.off();
    }
}