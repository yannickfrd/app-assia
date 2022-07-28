require('../scss/app.scss')
import PersonSearcher from './PersonSearcher'
import autoLogout from './utils/autoLogout'
import {Popover, Toast, Tooltip} from '../../node_modules/bootstrap'

require('../../node_modules/bootstrap')
require('@fortawesome/fontawesome-free/css/all.min.css')

window.onload = () => {
    if (document.getElementById('navbar')) {
        new PersonSearcher('#search-person')
        new autoLogout(40, 30) // minutes

        // Bootstrap
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(tooltip => new Tooltip(tooltip))
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(popover => new Popover(popover))
        document.querySelectorAll('.toast').forEach(toast => new Toast(toast).show())
    }
    // Stop the spinner loader 
    document.getElementById('loader').classList.add('d-none')
}