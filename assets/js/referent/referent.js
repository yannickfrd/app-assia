import SearchLocation from '../utils/searchLocation'
import '../utils/maskZipcode'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    new SearchLocation('referent_location')
})