import AjaxRequest from "../utils/ajaxRequest";
import SelectRadioJS from "../utils/selectRadio";
import UpdateSupportPeople from "./updateSupportPeople";
import CheckChange from "../utils/checkChange";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", e => {
    new SelectRadioJS("table-support-people");
    new UpdateSupportPeople(ajaxRequest);
    new CheckChange("support_group_with_people"); // form name
});