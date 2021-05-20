import changeChecker from '../utils/form/changeChecker'
import FieldDisplayer from '../utils/form/fieldDisplayer'

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('div[data-parent-field]').forEach(elt => {
        new FieldDisplayer(elt)
    })    
    new changeChecker('device') // form name
})