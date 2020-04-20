import AjaxRequest from "../utils/ajaxRequest";
import Evaluation from "./evaluation";
import UpdateEvaluation from "./updateEvaluation";
import CheckChange from "../utils/checkChange";
import "../utils/maskZipcode";

let ajaxRequest = new AjaxRequest();

document.querySelectorAll("div.card-header").forEach(cardHeaderElt => {
    let spanFaElt = cardHeaderElt.querySelector("span.fa");
    cardHeaderElt.addEventListener("click", e => {
        if (cardHeaderElt.classList.contains("collapsed")) {
            spanFaElt.classList.replace("fa-chevron-right", "fa-chevron-down");
        } else {
            spanFaElt.classList.replace("fa-chevron-down", "fa-chevron-right");
        }
    });
});

document.addEventListener("DOMContentLoaded", e => {
    new Evaluation();
    new UpdateEvaluation(ajaxRequest);
    new CheckChange("evaluation_group"); // form name
});