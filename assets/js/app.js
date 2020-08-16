require('../css/app.scss')
require('../css/table.scss')
require('../css/calendar.scss')
import 'select2/dist/css/select2.min.css'
import 'select2-bootstrap4-theme/dist/select2-bootstrap4.min.css'

const $ = require('jquery')

require('bootstrap')
// require('bootstrap-datepicker')

import AjaxRequest from './utils/ajaxRequest'
import SearchPerson from './searchPerson'
import autoLogout from './utils/autoLogout'

// Requête Ajax
let ajaxRequest = new AjaxRequest()

// Masque le l  oader lorsque le DOM est chargé
window.onload = () => {
    $(() => {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(() => {
        $('[data-toggle="popover"]').popover()
    })
    $('.toast').toast('show')

    // Stop spinner loader 
    document.getElementById('loader').classList.add('d-none')

    // Recherche instannée d'une personne via Ajax
    new SearchPerson(ajaxRequest, 3, 500) // lengthSearch, time

    // Déconnexion automatique de l'utilisateur
    new autoLogout(ajaxRequest, 40, 10) // minutes
}