import '../utils/maskPhone'
import CheckChange from '../utils/checkChange'
import Username from '../security/username'

document.addEventListener('DOMContentLoaded', () => {
    new CheckChange('user') // form name
    new Username('user')
})