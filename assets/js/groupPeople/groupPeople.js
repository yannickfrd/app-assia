import AjaxRequest from "../utils/ajaxRequest";
import SelectRadioJS from "../utils/selectRadio";
import RemovePerson from "./removePerson";

let ajaxRequest = new AjaxRequest();

new SelectRadioJS("table-people");
new RemovePerson(ajaxRequest);