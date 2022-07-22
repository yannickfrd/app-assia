import LocationSearcher from '../utils/LocationSearcher'
import DeletePlace from './deletePlace'
import changeChecker from '../utils/form/changeChecker'

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-location-search]').forEach(elt => new LocationSearcher(elt))
    new DeletePlace()
    new changeChecker('place') // form name
})