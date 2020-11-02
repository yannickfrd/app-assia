import Loader from './utils/loader'
import AjaxRequest from './utils/ajaxRequest'

/**
 * Recherche instannée Ajax.
 */
export default class SearchPerson {

    constructor(lengthSearch = 3, time = 500) {
        this.loader = new Loader()
        this.ajaxRequest = new AjaxRequest(this.loader)
        this.searchElt = document.getElementById('search-person')
        this.resultsSearchElt = document.getElementById('results_search')
        this.lengthSearch = lengthSearch
        this.time = time
        this.countdownID = null
        this.init()
    }
    
    init() {
        if (this.searchElt) {
            this.searchElt.addEventListener('keyup', this.timer.bind(this))
        }
    }

    /**
     * Timer avant de lancer la requête Ajax.
     */
    timer() {
        this.searchElt.value = this.searchElt.value.replace("	", " ").replace("  ", " ")
        clearInterval(this.countdownID)
        this.countdownID = setTimeout(this.count.bind(this), this.time)
    }

    /**
     * Compte le nombre de caratères saisis et lance la requête Ajax<.
     */
    count() {
        let valueSearch = this.searchElt.value
        if (valueSearch.length >= this.lengthSearch) {
            this.loader.on()
            let url = '/search/person?search=' + valueSearch
            this.ajaxRequest.send('GET', url, this.response.bind(this), true)
        }
    }

    /**
     * Affiche les résultats de la rêquête.
     * @param {Object} data 
     */
    response(data) {
        this.resultsSearchElt.innerHTML = ''
        if (data.people.length > 0) {
            this.addItem(data)
        } else {
            this.noResult()
        }
        this.resultsSearchElt.classList.replace('d-none', 'd-block')
        this.resultsSearchElt.classList.replace('fade-out', 'fade-in')
        this.loader.off()

        document.addEventListener('click', e => {
            if (e.target.id != 'search-person') {
                this.hideListResults()
            }
        })
    }

    /**
     * Ajoute un élément à la liste des résultats.
     * @param {Object} data 
     */
    addItem(data) {
        data.people.forEach(person => {
            let aElt = document.createElement('a')
            aElt.innerHTML = `<span class="text-capitalize text-secondary small">${person.fullname}</span>`
            aElt.href = '/person/' + person.id
            aElt.className = 'list-group-item list-group-item-action pl-3 pr-1 py-1'
            this.resultsSearchElt.appendChild(aElt)
            aElt.addEventListener('click', () => {
                this.loader.on()
            })
        })
    }

    /**
     * Affiche 'Aucun résultat.'.
     */
    noResult() {
        let spanElt = document.createElement('p')
        spanElt.textContent = 'Aucun résultat.'
        spanElt.className = 'list-group-item pl-3 py-2'
        this.resultsSearchElt.appendChild(spanElt)
    }

    /**
     * Supprime la liste des résultats au clic.
     */
    hideListResults() {
        this.resultsSearchElt.classList.replace('fade-in', 'fade-out')
        this.resultsSearchElt.classList.replace('d-block', 'd-none')
    }
}