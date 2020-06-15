import AjaxRequest from "../utils/ajaxRequest";
import SupportNotes from "./supportNotes";
import CheckChangeModal from "../utils/checkChangeModal";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", () => {
    new SupportNotes(ajaxRequest);
    // new CheckChangeModal("note"); // form name
});