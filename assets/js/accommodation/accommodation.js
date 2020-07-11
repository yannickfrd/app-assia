import SearchLocation from "../utils/searchLocation";
import DeleteAccommodation from "./deleteAccommodation";
import CheckChange from "../utils/checkChange";

document.addEventListener("DOMContentLoaded", () => {
    new SearchLocation("accommodation_location");
    new DeleteAccommodation();
    new CheckChange("accommodation"); // form name
});