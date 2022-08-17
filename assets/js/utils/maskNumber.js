/** 
 * Masque de saisie pour un nombre ou identiant
 */
 document.addEventListener('DOMContentLoaded', () => {
    const regex = /[^0-9]/g;

     document.querySelectorAll('input[data-mask-type="number"]').forEach(inpuElt => {
        if (inpuElt.hasAttribute('maxlength') === false) {
            inpuElt.setAttribute('maxlength', 12)
        }
        
        inpuElt.addEventListener('input', () => {
            if (inpuElt.value.match(regex)) {
                inpuElt.value = inpuElt.value.replace(regex, '')
            }
        })
    })
})