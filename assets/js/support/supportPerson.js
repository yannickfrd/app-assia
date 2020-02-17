import AjaxRequest from "../utils/ajaxRequest";
import SelectRadioJS from "../utils/selectRadio";
import RemoveSupportPerson from "./removeSupportPerson";

let ajaxRequest = new AjaxRequest();
new SelectRadioJS("table-support-people");
new RemoveSupportPerson(ajaxRequest);