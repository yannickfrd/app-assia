import AjaxRequest from "../utils/ajaxRequest";
// import MessageFlash from "../utils/messageFlash";
// import ValidationPerson from "./validationPerson";

import UpdatePerson from "./updatePerson";
import NewGroupPeople from "./newGroupPeople";
import ParametersUrl from "../utils/parametersUrl";
import "../utils/maskPhone";

let ajaxRequest = new AjaxRequest();
let parametersUrl = new ParametersUrl();

document.addEventListener("DOMContentLoaded", function () {
    let editMode = document.getElementById("person").dataset.editMode;
    if (editMode === "true") {
        let updatePerson = new UpdatePerson(ajaxRequest);
    } else {
        let newGroupPeople = new NewGroupPeople(parametersUrl);
    }
});