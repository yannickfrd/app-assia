import SupportNotes from "./supportNotes";
import ScrollAnimation from "../utils/scrollAnimation";

document.addEventListener("DOMContentLoaded", () => {
    new SupportNotes();
    (new ScrollAnimation()).init();
});