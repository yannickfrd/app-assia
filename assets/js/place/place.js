import SearchLocation from '../utils/searchLocation'
import DeletePlace from './deletePlace'
import changeChecker from '../utils/form/changeChecker'

document.addEventListener('DOMContentLoaded', () => {
    new SearchLocation('place_location')
    new DeletePlace()
    new changeChecker('place') // form name
})