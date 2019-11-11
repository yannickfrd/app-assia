import AjaxRequest from "../utils/ajaxRequest";
// import MessageFlash from "../utils/messageFlash";

import SelectRadioJS from "./selectRadio";
import RemovePerson from "./removePerson";

let ajaxRequest = new AjaxRequest();

let selectRadioJS = new SelectRadioJS();
let removePerson = new RemovePerson(ajaxRequest);