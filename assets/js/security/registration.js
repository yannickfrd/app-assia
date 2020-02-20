import Username from "./username";
import SeePassword from "./seePassword";
import DeleteTr from "../utils/deleteTr";
import CheckChange from "../utils/checkChange";
import "../utils/addCollectionWidget";
import "../utils/maskPhone";

document.addEventListener("DOMContentLoaded", function () {
    new SeePassword();
    new DeleteTr("function-table");
    new Username("registration");
    new CheckChange("registration"); // form name
});