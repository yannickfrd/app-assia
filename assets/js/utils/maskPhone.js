import 'jquery-mask-plugin'

/** 
 * Masque de saisie pour le numéro de téléphone
 */
document.addEventListener('DOMContentLoaded', () => {
    $('.js-phone').mask('99 99 99 99 99', {
        placeholder: '__ __ __ __ __'
    })
})