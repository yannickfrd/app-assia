import DeleteTr from '../utils/deleteTr'
import changeChecker from '../utils/form/changeChecker'
import AddCollectionWidget from '../utils/addCollectionWidget'
import FieldDisplayer from '../utils/form/fieldDisplayer'
import SearchLocation from '../utils/searchLocation'
import TagService from '../tag/TagService'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('div[data-parent-field]').forEach(elt => {
        new FieldDisplayer(elt)
    })

    new DeleteTr('function-table')
    new changeChecker('service') // form name
    new AddCollectionWidget()
    new SearchLocation('service_location')
    new TagService()
})
