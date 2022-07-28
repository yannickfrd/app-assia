import LocationSearcher from './utils/LocationSearcher'

document.querySelectorAll('[data-location-search]').forEach(elt => new LocationSearcher(elt))