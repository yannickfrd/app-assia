import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";

export default class ExportData {

    constructor(rRequest) {
        this.ajaxRequest = ajaxRequest;
        this.formElt = document.querySelector("#form-search>form");
        this.btnSubmitElts = this.formElt.querySelectorAll("button[type='submit']");
        this.loader = new Loader();
        this.init();
    }

    init() {
        this.btnSubmitElts.forEach(btnSubmitElt => {
            btnSubmitElt.addEventListener("click", e => {
                this.loader.on();
                e.preventDefault();
                let formToString = new URLSearchParams(new FormData(this.formElt)).toString();
                this.ajaxRequest.init("POST", btnSubmitElt.getAttribute("data-url"), this.response.bind(this), true, formToString);
            });
        })
    }

    response(response) {
        let data = JSON.parse(response);
        this.loader.off();
        new MessageFlash(data.alert, data.msg);
    }
}