import Ajax from './ajax'
import Loader from './loader'

/**
 * Control the search of an address or a city with API adresse.data.gouv.fr.
 */
export default class LocationSearcher {
    /**
     * @param {HTMLElement} containerElt 
     */
    constructor(containerElt) {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.containerElt = containerElt
        
        this.locationType = containerElt.dataset.locationSearch ?? 'address'
        this.lengthSearch = containerElt.dataset.locationSearchLength ?? 3 // nb  keys before to launch the search
        this.delay = containerElt.dataset.locationSearchDelay ?? 500 // delay in milliseconds
        this.limit = containerElt.dataset.locationSearchLimit ?? 5 // number elements into the list
        this.lat = containerElt.dataset.locationLat
        this.lon = containerElt.dataset.locationLon

        this.lat = this.lat !== '' ? this.lat : 48.85 // latitude
        this.lon = this.lon !== '' ? this.lon : 2.35 // longitude
        this.results = null
        this.countdownID = null

        this.searchElt = containerElt.querySelector('[data-location-type="search"]')
        this.addressElt = containerElt.querySelector('[data-location-type="address"]')
        this.cityElt = containerElt.querySelector('[data-location-type="city"]')
        this.zipcodeElt = containerElt.querySelector('[data-location-type="zipcode"]')
        this.locationIdElt = containerElt.querySelector('[data-location-type="locationId"]')
        this.latElt = containerElt.querySelector('[data-location-type="lat"]')
        this.lonElt = containerElt.querySelector('[data-location-type="lon"]')

        this.resultsSearchElt = this.createResultsListElt()

        this.init()
    }

    init() {
        this.searchElt.addEventListener('keyup', this.timer.bind(this))
    }

    /**
     * Create the list with results
     * 
     * @return {HTMLDivElement}
     */
    createResultsListElt() {
        const resultsListElt = document.createElement('div')
        resultsListElt.id = 'results_list_location'
        resultsListElt.className = 'w-100 list-group d-block fade-in position-absolute z-index-999'
        if (this.cityElt) {
            this.searchElt.parentNode.appendChild(resultsListElt)
        } else {
            this.searchElt.parentNode.parentNode.appendChild(resultsListElt)
        }

        return resultsListElt
    }

    /**
     * Timer before to launch ajax request
     */
    timer() {
        clearInterval(this.countdownID)
        this.countdownID = setTimeout(this.count.bind(this), this.delay)
    }

    /**
     * Count number of Compte le nombre de caractères saisis et lance la requête Ajax.
     */
    count() {
        if (this.searchElt.value.length >= this.lengthSearch) {
            this.loader.on()
            this.ajax.send('GET', this.getUrl(), this.responseAjax.bind(this))
        }
    }

    /**
     * @returns {String}
     */
    getUrl() {
        const valueSearch = this.searchElt.value.replace(' ', '+')
        const geo = `&lat=${this.lat}&lon=${this.lon}` // Get the geo priority with lat and lon
        let url = 'https://api-adresse.data.gouv.fr/search/?q='
        const codeDepartment = this.containerElt.dataset.locationDept ?? null

        if (codeDepartment) {
            return 'https://geo.api.gouv.fr/communes?nom=' + valueSearch + '&codeDepartement=' + codeDepartment + '&limit=' + this.limit
        }

        if (this.locationType === 'city') {
            url = url + valueSearch + '&type=municipality' + '&autocomplete=1' + '&limit=' + this.limit
        }

        return url + valueSearch + geo + '&limit=' + this.limit
    }

    /**
     * Get the Ajax response.
     * 
     * @param {Object} data 
     */
    responseAjax(data) {
        if (this.containerElt.dataset.locationDept) {
            this.results = data
        } else {
            this.results = data.features
        }

        this.resultsSearchElt.innerHTML = ''
        if (this.results.length > 0) {
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
     * Add an item into the list of results.
     */
    addItem() {
        let i = 0
        this.results.forEach(result => {
            let itemElt = this.createItem(result, i)
            this.resultsSearchElt.appendChild(itemElt)
            itemElt.addEventListener('click', () => this.updateLocation(itemElt.dataset.result))
            i++
        })
        this.setWidthResultsSearchElt()
    }

    /**
     * Create a item.
     * 
     * @param {Array} result 
     * @param {Number} i 
     * @return {HTMLElement}
     */
    createItem(result, i) {
        const itemElt = document.createElement('a')
        itemElt.innerHTML = `<span class='text-secondary small'>${this.getLabel(result)}</span>`
        itemElt.className = 'list-group-item list-group-item-action ps-3 pe-1 py-1 cursor-pointer'
        itemElt.dataset.result = i

        return itemElt
    }

    /**
     * Get the label of the search.
     * 
     * @param {Array} result 
     */
    getLabel(result) {
        if (this.containerElt.dataset.locationDept) {
            return `${result.nom} (${result.codesPostaux[0]})`
        }

        if (this.locationType === 'city') {
            return `${result.properties.city} (${result.properties.postcode})`
        }

        return `${result.properties.label} (${result.properties.context})`
    }

    /**
     * Edit the width of the results list element.
     */
    setWidthResultsSearchElt() {
        const styleSeachElt = window.getComputedStyle(this.searchElt)
        this.resultsSearchElt.style.maxWidth = styleSeachElt.width
        this.resultsSearchElt.style.top = styleSeachElt.height
    }

    /**
     * Update the input fields.
     * 
     * @param {Number} i 
     */
    updateLocation(i) {
        const result = this.results[i]

        if (this.containerElt.dataset.locationDept) {
            return this.cityElt.value = result.nom
        }
        this.searchElt.value = result.properties.label
        if (result.properties.city && this.cityElt) {
            this.cityElt.value = result.properties.city
        }
        if (this.zipcodeElt) {
            this.zipcodeElt.value = result.properties.postcode
        }
        if (this.addressElt) {
            this.addressElt.value = result.properties.name
        }
        if (this.locationIdElt) {
            this.locationIdElt.value = result.properties.id
            this.lonElt.value = result.geometry.coordinates[0]
            this.latElt.value = result.geometry.coordinates[1]
        }
    }

    /**
     * Display 'no result'.
     */
    displayNoResult() {
        const spanElt = document.createElement('p')
        spanElt.textContent = 'Aucun résultat.'
        spanElt.className = 'list-group-item ps-3 py-2 text-secondary small'
        this.resultsSearchElt.appendChild(spanElt)
    }

    /**
     * Hide the results list with click.
     */
    hideListResults() {
        this.resultsSearchElt.classList.replace('fade-in', 'fade-out')
        setTimeout(() => {
            this.resultsSearchElt.classList.replace('d-block', 'd-none')
        }, 300)
    }
}