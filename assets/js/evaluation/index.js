import Evaluation from "./evaluation";
import UpdateEvaluation from "./updateEvaluation";
import CheckChange from "../utils/checkChange";
import SearchLocation from "../utils/searchLocation";
import "../utils/accordionChevron";
// import SpeedTest from "../utils/speedTest";
document.addEventListener("DOMContentLoaded", () => {
    new Evaluation();
    new UpdateEvaluation();
    new CheckChange("evaluation"); // form name
    new SearchLocation("domiciliation_location");
    // new SpeedTest(null, 100);
});