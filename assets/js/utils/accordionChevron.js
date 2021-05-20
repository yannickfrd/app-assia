let collapseIsMoving = false

// Permet de modifier l'icon du chevron (ouvert ou fermé)
document.querySelectorAll('div.card-header').forEach(cardHeaderElt => {
    cardHeaderElt.addEventListener('click', () => changeChevron(cardHeaderElt))
})

document.querySelectorAll('button[data-btn="reduce"]').forEach(btnElt => {
    btnElt.addEventListener('click', e => {
        e.preventDefault()
        changeChevron(document.querySelector(`div.card-header[data-target="${btnElt.dataset.target}"]`))
    })
})

/**
 * @param {HTMLElement} divElt 
 */
function changeChevron(divElt) {
    const chevronElt = divElt.querySelector('span.fa')
    if (!collapseIsMoving) {
        if (divElt.classList.contains('collapsed')) {
            collapseIsMoving = true;
            setTimeout(() => {
                collapseIsMoving = false
            }, 400)
            return chevronElt.classList.add('rotation-90')
        }

        chevronElt.classList.add('rotation-0')
        setTimeout(() => {
            chevronElt.classList.remove('rotation-0')
            chevronElt.classList.remove('rotation-90')
            collapseIsMoving = false
        }, 400)
    }
}
