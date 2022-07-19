import TomSelect from 'tom-select'

document.querySelectorAll('select[autocomplete]').forEach(selectElt => {
    new TomSelect(selectElt)
})