import SelectType from '../utils/selectType'

const selectType = new SelectType();
const yearSelectElt = document.getElementById('year');
const startInputElt = document.getElementById('date_start');
const endInputElt = document.getElementById('date_end');

yearSelectElt.addEventListener('click', () => {
    const value = selectType.getOption(yearSelectElt);
    if (value) {
        startInputElt.value = value + '-01-01';
        endInputElt.value = (value + 1) + '-01-01';
    }
})