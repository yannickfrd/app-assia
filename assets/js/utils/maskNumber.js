import 'jquery-mask-plugin'

/** 
 * Masque de saisie pour un nombre/identiant
 */
document.addEventListener('DOMContentLoaded', () => {
    $('[data-mask-type]').mask('999999999999')
})