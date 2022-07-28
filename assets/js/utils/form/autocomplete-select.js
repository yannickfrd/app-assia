import SelectManager from "./SelectManager";

document.querySelectorAll('select[autocomplete="true"]').forEach(selectElt => {
    new SelectManager(selectElt)
})