import AjaxRequest from "../utils/ajaxRequest";
import ListNotes from "./listNotes";
import CheckChangeModal from "../utils/checkChangeModal";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", e => {
    new ListNotes(ajaxRequest);
    // new CheckChangeModal("note"); // form name
});