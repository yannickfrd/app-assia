import AjaxRequest from "../utils/ajaxRequest";
// import ValidationPerson from "./validationPerson";
import UpdatePerson from "./updatePerson";
import NewGroupPeople from "./newGroupPeople";
import ParametersUrl from "../utils/parametersUrl";
import CheckChange from "../utils/checkChange";
import "../utils/maskPhone";

let ajaxRequest = new AjaxRequest();
let parametersUrl = new ParametersUrl();

document.addEventListener("DOMContentLoaded", function () {
    let editMode = document.getElementById("person").dataset.editMode;
    if (editMode === "true") {
        new UpdatePerson(ajaxRequest);
        new CheckChange("person"); // form name
    } else {
        new NewGroupPeople(parametersUrl);
        new CheckChange("person_role_person"); // form name
    }
});