import '../utils/maskPhone'
import changeChecker from '../utils/form/changeChecker'
import Username from '../security/username'

document.addEventListener('DOMContentLoaded', () => {
    new changeChecker('user') // form name
    new Username('user')
})