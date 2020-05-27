import AjaxRequest from "../utils/ajaxRequest";
import SupportContributions from "./supportContributions";
import CheckChangeModal from "../utils/checkChangeModal";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", e => {
    new SupportContributions(ajaxRequest);
    // new CheckChangeModal("contribution"); // form name
});