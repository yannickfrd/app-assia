// Permet de modifier l'icon du chevron (ouvert ou fermé) 
document.querySelectorAll('div.card-header').forEach(cardHeaderElt => {
    cardHeaderElt.addEventListener('click', () => changeChevron(cardHeaderElt))
})

document.querySelectorAll('button[data-btn="reduce"]').forEach(btnElt => {
    btnElt.addEventListener('click', e => {
        e.preventDefault()
        changeChevron(document.querySelector(`div.card-header[data-target="${btnElt.getAttribute('data-target')}"]`))
    })
})

/**
 * @param {HTMLElement} divElt 
 */
function changeChevron(divElt) {
    const chevronElt = divElt.querySelector('span.fa')
    if (divElt.classList.contains('collapsed')) {
        chevronElt.classList.replace('fa-chevron-right', 'fa-chevron-down')
    } else {
        chevronElt.classList.replace('fa-chevron-down', 'fa-chevron-right')
    }
}