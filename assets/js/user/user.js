import "../utils/maskPhone";
import CheckChange from "../utils/checkChange";
import Username from "../security/username";

document.addEventListener("DOMContentLoaded", e => {
    new CheckChange("user_change_info"); // form name
    new Username("user_change_info");
});