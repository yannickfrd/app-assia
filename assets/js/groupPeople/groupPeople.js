import SelectRadioJS from "../utils/selectRadio";
import RemovePerson from "./removePerson";
import NewSupport from "./newSupport";
import CheckChange from "../utils/checkChange";

document.addEventListener("DOMContentLoaded", () => {
    new SelectRadioJS("table-people");
    new RemovePerson();
    new NewSupport();
    new CheckChange("group"); // form name
});