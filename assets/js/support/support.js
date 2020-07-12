import ValidationSupport from "./validationSupport";
import AjaxRequest from "../utils/ajaxRequest";
import SelectRadioJS from "../utils/selectRadio";
import UpdateSupportPeople from "./updateSupportPeople";
import SearchLocation from "../utils/searchLocation";
import CheckChange from "../utils/checkChange";

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("support")) {
        new SelectRadioJS("table-support-people");
        new UpdateSupportPeople(new AjaxRequest());
    }
    new SearchLocation("support_location");
    new ValidationSupport();
    new CheckChange("support"); // form name
});