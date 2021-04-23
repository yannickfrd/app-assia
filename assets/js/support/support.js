import SupportValidator from './supportValidator'
import AvdlValidator from './avdlValidator'
import HotelSupportValidator from './hotelSupportValidator'
import RadioSelecter from '../utils/form/radioSelecter'
import RemoveTableRow from '../utils/removeTableRow'
import SearchLocation from '../utils/searchLocation'
import changeChecker from '../utils/form/changeChecker'
import AutoSizer from '../utils/form/autoSizer'

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('support')) {
        new RadioSelecter('table-support-people')
        new RemoveTableRow('.js-tr-support_pers')
    }

    if (document.getElementById('avdl_support')) {
        new AvdlValidator()
    } else if (document.getElementById('hotel_support')) {
        new HotelSupportValidator()
    }

    new SupportValidator()

    new SearchLocation('support_location')
    new SearchLocation('ssd_location', 'city', '95')
    new SearchLocation('support_end_location')
    new changeChecker('support') // form name
    new AutoSizer('textarea')
})