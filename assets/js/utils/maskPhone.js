import Cleave from 'cleave.js'
import 'cleave.js/dist/addons/cleave-phone.fr.js'

/** 
 * Masque de saisie pour le numéro de téléphone
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-phone]').forEach(inpuElt => {
        inpuElt.setAttribute('maxlength', 14)
        new Cleave(inpuElt, {
            phone: true,
            phoneRegionCode: 'fr'
        })
    })
})