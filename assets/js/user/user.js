import '../utils/maskPhone'
import changeChecker from '../utils/form/changeChecker'
import Username from '../security/username'
import FieldDisplayer from '../utils/form/fieldDisplayer'

document.addEventListener('DOMContentLoaded', () => {
    new changeChecker('user') // form name
    new Username('user')

    document.querySelectorAll('div[data-parent-field]').forEach(elt => {
        new FieldDisplayer(elt)
    })
})