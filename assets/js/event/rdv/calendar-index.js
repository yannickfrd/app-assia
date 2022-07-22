import CalendarManager from './CalendarManager'
import LocationSearcher from '../../utils/LocationSearcher'

window.addEventListener('load', () => {
    new CalendarManager()
    
    document.querySelectorAll('[data-location-search]').forEach(elt => new LocationSearcher(elt))
})