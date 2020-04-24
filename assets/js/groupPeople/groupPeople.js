import AjaxRequest from "../utils/ajaxRequest";
import SelectRadioJS from "../utils/selectRadio";
import RemovePerson from "./removePerson";
import CheckChange from "../utils/checkChange";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", e => {
    new SelectRadioJS("table-people");
    new RemovePerson(ajaxRequest);
    new CheckChange("group"); // form name
});