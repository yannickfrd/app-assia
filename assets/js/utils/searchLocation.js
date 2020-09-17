import AjaxRequest from '../utils/ajaxRequest'
import Loader from './loader'

/**
 * Gère la recherche d'une adresse avec l'API adresse.data.gouv.fr
 */
export default class SearchLocation {

    constructor(containerId, lengthSearch = 3, time = 500, limit = 5, lat = 49.04, lon = 2.04) {
        this.containerElt = document.getElementById(containerId)
        if (this.containerElt) {
            this.ajaxRequest = new AjaxRequest()
            this.searchElt = this.containerElt.querySelector('.js-search')
            this.addressElt = this.containerElt.querySelector('.js-address')
            this.cityElt = this.containerElt.querySelector('.js-city')
            this.zipcodeElt = this.containerElt.querySelector('.js-zipcode')
            this.locationIdElt = this.containerElt.querySelector('.js-locationId')
            this.latElt = this.containerElt.querySelector('.js-lat')
            this.lonElt = this.containerElt.querySelector('.js-lon')
            this.resultsSearchElt = this.createResultsListElt()
            this.lengthSearch = lengthSearch // Nombre de caractères minimum pour lancer la recherche
            this.time = time // Durée en millisecondes
            this.limit = limit // Nombre d'éléments retournés
            this.lat = lat // Latitude
            this.lon = lon // Longitude
            this.features = null
            this.countdownID = null
            this.loader = new Loader()
            this.init()
        }
    }

    init() {
        this.searchElt.addEventListener('keyup', this.timer.bind(this))
    }

    /**
     * Crée la liste des résultats.
     * @return {HTMLDivElement}
     */
    createResultsListElt() {
        let resultsListElt = document.createElement('div')
        resultsListElt.id = 'results_list_location'
        resultsListElt.className = 'w-100 list-group d-block fade-in position-absolute z-index-1000'
        if (this.addressElt) {
            this.searchElt.parentNode.appendChild(resultsListElt)
        } else {
            this.searchElt.parentNode.parentNode.appendChild(resultsListElt)
        }

        return resultsListElt
    }

    /**
     * Timer avant de lancer la requête Ajax.
     */
    timer() {
        clearInterval(this.countdownID)
        this.countdownID = setTimeout(this.count.bind(this), this.time)
    }

    /**
     * Compte le nombre de caractères saisis et lance la requête Ajax.
     */
    count() {
        let valueSearch = this.searchElt.value
        if (valueSearch.length >= this.lengthSearch) {
            this.loader.on()
            let valueSearch = this.searchElt.value.replace(' ', '+')
            let geo = `&lat=${this.lat}&lon=${this.lon}` // Donne une priorité géographique
            let url = 'https://api-adresse.data.gouv.fr/search/?q=' + valueSearch + geo + '&limit=' + this.limit
            this.ajaxRequest.init('GET', url, this.responseAjax.bind(this), true)
        }
    }

    /**
     * Donne la réponse Ajax.
     * @param {JSON} response 
     */
    responseAjax(response) {
        this.features = JSON.parse(response).features
        this.resultsSearchElt.innerHTML = ''
        if (this.features.length > 0) {
            this.addItem()
        } else {
            this.displayNoResult()
        }
        this.resultsSearchElt.classList.replace('d-none', 'd-block')
        this.resultsSearchElt.classList.replace('fade-out', 'fade-in')
        this.loader.off()

        document.addEventListener('click', e => {
            if (e.target.id != 'support_location_search') {
                this.hideListResults()
            }
        })
    }

    /**
     * Ajoute un élément à la liste des résultats.
     */
    addItem() {
        let i = 0
        this.features.forEach(feature => {
            let itemElt = this.createItem(feature, i)
            this.resultsSearchElt.appendChild(itemElt)
            itemElt.addEventListener('click', () => {
                this.updateLocation(itemElt.getAttribute('data-feature'))
            })
            i++
        })
        this.setWidthResultsSearchElt()
    }

    /**
     * Créé un élément de résultat.
     * @param {Array} feature 
     * @param {Number} i 
     * @return {HTMLElement}
     */
    createItem(feature, i) {
        let itemElt = document.createElement('a')
        itemElt.innerHTML = `<span class='text-secondary small'>${this.getLabel(feature)}</span>`
        itemElt.className = 'list-group-item list-group-item-action pl-3 pr-1 py-1 cursor-pointer'
        itemElt.setAttribute('data-feature', i)

        return itemElt
    }

    /**
     * Donne le label de la recherche.
     * @param {Array} feature 
     */
    getLabel(feature) {
        let label = feature.properties.label
        if (feature.properties.type === 'municipality') {
            label = label + ' (' + feature.properties.context + ')'
        }
        return label
    }

    /**
     * Modifie la largeur de l'élément avec la liste des résultats.
     */
    setWidthResultsSearchElt() {
        let styleSeachElt = window.getComputedStyle(this.searchElt)
        this.resultsSearchElt.style.maxWidth = styleSeachElt.width
        this.resultsSearchElt.style.top = styleSeachElt.height
    }

    // Met à jour les champs du formulaire.
    updateLocation(i) {
        let feature = this.features[i]
        this.searchElt.value = feature.properties.label
        if (this.addressElt) {
            this.addressElt.value = feature.properties.name
            this.cityElt.value = feature.properties.city
            this.zipcodeElt.value = feature.properties.postcode
        }
        if (this.locationIdElt) {
            this.locationIdElt.value = feature.properties.id
            this.lonElt.value = feature.geometry.coordinates[0]
            this.latElt.value = feature.geometry.coordinates[1]
        }
    }

    /**
     * Affiche 'Aucun résultat.'
     */
    displayNoResult() {
        let spanElt = document.createElement('p')
        spanElt.textContent = 'Aucun résultat.'
        spanElt.className = 'list-group-item pl-3 py-2 text-secondary small'
        this.resultsSearchElt.appendChild(spanElt)
    }

    /**
     * Supprime la liste des résultats au clic.
     */
    hideListResults() {
        this.resultsSearchElt.classList.replace('fade-in', 'fade-out')
        setTimeout(() => {
            this.resultsSearchElt.classList.replace('d-block', 'd-none')
        }, 300)
    }
}