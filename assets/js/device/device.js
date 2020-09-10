import CheckChange from '../utils/checkChange'
import UpdateDevice from './updateDevice'

document.addEventListener('DOMContentLoaded', () => {
    new CheckChange('device') // form name
    new UpdateDevice()
})