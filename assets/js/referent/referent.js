import LocationSearcher from '../utils/LocationSearcher'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-location-search]').forEach(elt => new LocationSearcher(elt))
})