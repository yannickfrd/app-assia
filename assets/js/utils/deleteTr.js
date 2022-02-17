// Permet de retirer une ligne d'un tableau
export default class DeleteTr {

    /**
     * @param {string} tableId 
     */
    constructor(tableId) {
        this.trElts = document.querySelectorAll('#' + tableId + '>tbody>tr')
        this.init()
    }

    init() {
        this.trElts.forEach(trElt => {
            const btnElt = trElt.querySelector('button[data-action="remove"]')
            if (btnElt) {
                btnElt.addEventListener('click', e => {
                    e.preventDefault()
                    trElt.classList.add('fade-out')
                    setTimeout(() => {
                        trElt.remove()
                    }, 200)
                })
            }
        })
    }
}