import changeChecker from '../utils/form/changeChecker'
import UpdateDevice from './updateDevice'

document.addEventListener('DOMContentLoaded', () => {
    new changeChecker('device') // form name
    new UpdateDevice()
})