import DeleteTr from "../utils/deleteTr";
import CheckChange from "../utils/checkChange";
import AddCollectionWidget from "../utils/addCollectionWidget";
import "../utils/maskPhone";
import "../utils/maskZipCode";

document.addEventListener("DOMContentLoaded", function () {
    new DeleteTr("function-table");
    new CheckChange("service"); // form name
    new AddCollectionWidget();
});