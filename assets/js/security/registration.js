import Username from "./username";
import SeePassword from "./seePassword";
import DeleteTr from "../utils/deleteTr";
import CheckChange from "../utils/checkChange";
import AddCollectionWidget from "../utils/addCollectionWidget";
import "../utils/maskPhone";

document.addEventListener("DOMContentLoaded", e => {
    new SeePassword();
    new DeleteTr("function-table");
    new Username("security_user");
    new CheckChange("security_user"); // form name
    let addCollectionWidget = new AddCollectionWidget();

    if (parseInt(addCollectionWidget.counter) === 0) {
        addCollectionWidget.addElt();
    }
});