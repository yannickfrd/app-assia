import AjaxRequest from "../utils/ajaxRequest";
import Evaluation from "./evaluation";
import UpdateEvaluation from "./updateEvaluation";
import CheckChange from "../utils/checkChange";
import "../utils/accordionChevron";
import "../utils/maskZipcode";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", () => {
    new Evaluation();
    new UpdateEvaluation(ajaxRequest);
    new CheckChange("evaluation"); // form name
});