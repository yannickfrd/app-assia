/**
 * Masque de saisie pour le code postal
 */
import 'jquery-mask-plugin'

document.addEventListener('DOMContentLoaded', () => {
    $('.js-zipcode').mask('99 999', {
        placeholder: '__ ___'
    })
})