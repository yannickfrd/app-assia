// import Username from "./username";
import SeePassword from "./seePassword";
import DeleteTr from "../utils/deleteTr";
import CheckChange from "../utils/checkChange";
import AddCollectionWidget from "../utils/addCollectionWidget";
import "../utils/maskPhone";

document.addEventListener("DOMContentLoaded", e => {
    // let username = new Username("security_user");
    new SeePassword();
    new DeleteTr("function-table");
    new CheckChange("user"); // form name
    new AddCollectionWidget();
});