import AjaxRequest from "../utils/ajaxRequest";
// import MessageFlash from "../utils/messageFlash";
// import ValidationPerson from "./validationPerson";

import UpdatePerson from "./updatePerson";
import NewGroupPeople from "./newGroupPeople";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", function () {
    let editModeElt = document.getElementById("person");
    let editMode = editModeElt.dataset.isEditMode;
    if (editMode === "true") {
        let updatePerson = new UpdatePerson(ajaxRequest);
    } else {
        let newGroupPeople = new NewGroupPeople();
    }
});