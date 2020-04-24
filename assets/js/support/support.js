import ValidationSupport from "./validationSupport";
import CheckChange from "../utils/checkChange";

document.addEventListener("DOMContentLoaded", e => {
    new ValidationSupport();
    new CheckChange("support"); // form name
});