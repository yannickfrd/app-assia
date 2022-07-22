import DeleteTr from '../utils/deleteTr'
import changeChecker from '../utils/form/changeChecker'
import WidgetCollectionManager from '../utils/form/WidgetCollectionManager'
import FieldDisplayer from '../utils/form/fieldDisplayer'
import LocationSearcher from '../utils/LocationSearcher'
import TagService from '../tag/TagService'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('div[data-parent-field]').forEach(elt => {
        new FieldDisplayer(elt)
    })

    new DeleteTr('function-table')
    new changeChecker('service') // form name
    new WidgetCollectionManager()
    new TagService()
    
    document.querySelectorAll('[data-location-search]').forEach(elt => new LocationSearcher(elt))
})
