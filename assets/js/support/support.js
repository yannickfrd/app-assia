import ValidationSupport from './validationSupport'
// import SwitchServiceSupport from './switchServiceSupport'
import ValidationAvdl from './validationAvdl'
import ValidationHotelSupport from './validationHotelSupport'
import SelectRadioJS from '../utils/selectRadio'
import RemoveTableRow from '../utils/removeTableRow'
import SearchLocation from '../utils/searchLocation'
import CheckChange from '../utils/checkChange'
import AutoSize from '../utils/AutoSize'

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('support')) {
        new SelectRadioJS('table-support-people')
        new RemoveTableRow('.js-tr-support_pers')
    }

        new ValidationSupport()
    // new SwitchServiceSupport()

    if (document.getElementById('avdl_support')) {
        new ValidationAvdl()
    } else if (document.getElementById('hotel_support')) {
        new ValidationHotelSupport()
    }
    new SearchLocation('support_location')
    new SearchLocation('ssd_location', 'city')
    new CheckChange('support') // form name
    new AutoSize('textarea')
})