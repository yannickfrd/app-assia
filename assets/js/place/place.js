import SearchLocation from '../utils/searchLocation'
import DeletePlace from './deletePlace'
import CheckChange from '../utils/checkChange'

document.addEventListener('DOMContentLoaded', () => {
    new SearchLocation('place_location')
    new DeletePlace()
    new CheckChange('place') // form name
})