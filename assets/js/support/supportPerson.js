import AjaxRequest from "../utils/ajaxRequest";
import SelectRadioJS from "../utils/selectRadio";
import RemoveSupportPerson from "./removeSupportPerson";
import CheckChange from "../utils/checkChange";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", e => {
    new SelectRadioJS("table-support-people");
    new RemoveSupportPerson(ajaxRequest);
    new CheckChange("support_group_with_people"); // form name
});