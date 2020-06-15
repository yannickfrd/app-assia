import DeleteAccommodation from "./deleteAccommodation";
import CheckChange from "../utils/checkChange";
import "../utils/maskZipcode";


document.addEventListener("DOMContentLoaded", () => {
    new DeleteAccommodation();
    new CheckChange("accommodation"); // form name
});