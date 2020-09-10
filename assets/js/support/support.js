import ValidationSupport from './validationSupport'
import ValidationAvdl from './validationAvdl'
import ValidationHotelSupport from './validationHotelSupport'
import SelectRadioJS from '../utils/selectRadio'
import RemoveTableRow from '../utils/removeTableRow'
import SearchLocation from '../utils/searchLocation'
import CheckChange from '../utils/checkChange'

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('support')) {
        new SelectRadioJS('table-support-people')
        new RemoveTableRow('.js-tr-support_pers')
    }
    if (document.getElementById('avdl_support')) {
        new ValidationAvdl()
    } else if (document.getElementById('hotel_support')) {
        new ValidationHotelSupport()
    } else {
        new ValidationSupport
    }
    new SearchLocation('support_location')
    new CheckChange('support') // form name
})