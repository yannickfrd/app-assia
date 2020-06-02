import DeleteTr from "../utils/deleteTr";
import CheckChange from "../utils/checkChange";
import AddCollectionWidget from "../utils/addCollectionWidget";
import UpdateService from "./updateService";
import "../utils/maskPhone";
import "../utils/maskZipcode";

document.addEventListener("DOMContentLoaded", e => {
    new DeleteTr("function-table");
    new CheckChange("service"); // form name
    new AddCollectionWidget();
    new UpdateService();
});