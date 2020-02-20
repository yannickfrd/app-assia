import "../utils/maskPhone";
import CheckChange from "../utils/checkChange";
import Username from "../security/username";

document.addEventListener("DOMContentLoaded", function () {
    new CheckChange("user_change_info"); // form name
    new Username("user");
});