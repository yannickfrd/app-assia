// import Username from "./username";
import SeePassword from "./seePassword";
import DeleteTr from "../utils/deleteTr";
import CheckChange from "../utils/checkChange";
import "../utils/maskPhone";
import "../utils/addCollectionWidget";


document.addEventListener("DOMContentLoaded", function () {
    // let username = new Username("security_user");
    new SeePassword();
    new DeleteTr("function-table");
    new CheckChange("security_user"); // form name
});