import SelectManager from "./SelectManager";

document.querySelectorAll('select[data-select="advanced"]').forEach(selectElt => {
    new SelectManager('#' + selectElt.id)
})