import AjaxRequest from "../utils/ajaxRequest";
import Evaluation from "./evaluation";
import UpdateEvaluation from "./updateEvaluation";
import CheckChange from "../utils/checkChange";
import "../utils/maskDeptCode";
import "../utils/maskPhone";

let ajaxRequest = new AjaxRequest();

document.querySelectorAll("div.card-header").forEach(cardHeaderElt => {
    let spanFaElt = cardHeaderElt.querySelector("span.fa");
    cardHeaderElt.addEventListener("click", function () {
        if (cardHeaderElt.classList.contains("collapsed")) {
            spanFaElt.classList.replace("fa-chevron-right", "fa-chevron-down");
        } else {
            spanFaElt.classList.replace("fa-chevron-down", "fa-chevron-right");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    new Evaluation();
    new UpdateEvaluation(ajaxRequest);
    new CheckChange("evaluation_group"); // form name
});