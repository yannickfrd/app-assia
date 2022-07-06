require('../css/app.scss')
import SearchPerson from './searchPerson'
import autoLogout from './utils/autoLogout'
import {Tooltip, Popover, Toast} from '../../node_modules/bootstrap'

require('../../node_modules/bootstrap')
require('@fortawesome/fontawesome-free/css/all.min.css')

// Lorsque le DOM est chargé
window.onload = () => {
    //Active les toggles Bootstrap
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(tooltip => {
        new Tooltip(tooltip)
    })
    document.querySelectorAll('[data-toggle="popover"]').forEach(popover => {
        new Popover(popover)
    })
    document.querySelectorAll('.toast').forEach(toast => {
        new Toast(toast).show()
    })
    // Stop le spinner loader 
    document.getElementById('loader').classList.add('d-none')
    // Recherche instannée d'une personne via Ajax
    new SearchPerson() // lengthSearch, time
    // Déconnexion automatique de l'utilisateur
    new autoLogout(60, 40) // minutes
}