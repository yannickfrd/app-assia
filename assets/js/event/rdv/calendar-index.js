require('../../../css/features/_calendar.scss')

import CalendarManager from './CalendarManager'
import SearchLocation from '../../utils/searchLocation'

window.addEventListener('load', () => {
    new CalendarManager()
    new SearchLocation('rdv_search_location')
})