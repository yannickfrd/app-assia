require('../css/app.scss')
require('../css/table.scss')
require('../css/calendar.scss')
import 'select2/dist/css/select2.min.css'
import 'select2-bootstrap4-theme/dist/select2-bootstrap4.min.css'
import SearchPerson from './searchPerson'
import autoLogout from './utils/autoLogout'

const $ = require('jquery')

require('bootstrap')
// require('bootstrap-datepicker')

// Lorsque le DOM est chargé
window.onload = () => {
    //Active les toggles Bootstrap
    $('[data-toggle="tooltip"]').tooltip()
    $('[data-toggle="popover"]').popover()
    $('.toast').toast('show')
    // Stop le spinner loader 
    document.getElementById('loader').classList.add('d-none')
    // Recherche instannée d'une personne via Ajax
    new SearchPerson() // lengthSearch, time
    // Déconnexion automatique de l'utilisateur
    new autoLogout(40, 10) // minutes
}