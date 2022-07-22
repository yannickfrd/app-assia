import SupportValidator from './supportValidator'
import AvdlValidator from './avdlValidator'
import HotelSupportValidator from './hotelSupportValidator'
import RadioSelecter from '../utils/form/radioSelecter'
import ConfirmAction from '../utils/confirmAction'
import LocationSearcher from '../utils/LocationSearcher'
import changeChecker from '../utils/form/changeChecker'
import AutoSizer from '../utils/form/autoSizer'
import '../utils/maskNumber'

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('support')) {
        new RadioSelecter('table-support-people')
        new ConfirmAction('tr[data-support-person]')
    }

    if (document.getElementById('avdl_support')) {
        new AvdlValidator()
    } else if (document.getElementById('hotel_support')) {
        new HotelSupportValidator()
    }

    new SupportValidator()

    document.querySelectorAll('[data-location-search]').forEach(elt => new LocationSearcher(elt))

    new changeChecker('support') // form name
    new AutoSizer('textarea')


})