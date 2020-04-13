import DeleteAccommodation from "./deleteAccommodation";
import CheckChange from "../utils/checkChange";
import "../utils/maskZipCode";


document.addEventListener("DOMContentLoaded", e => {
    new DeleteAccommodation();
    new CheckChange("evaluation_group"); // form name
});