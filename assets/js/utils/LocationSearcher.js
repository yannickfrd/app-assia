
import TomSelect from 'tom-select'

/**
 * Control the search of an address or a city with api-adresse.data.gouv.fr
 */
export default class LocationSearcher {
    /**
     * @param {HTMLElement} containerElt 
     * @param {Object} settings
     */
    constructor(containerElt, settings = {}) {
        this.url = 'https://api-adresse.data.gouv.fr/search/?q='

        this.containerElt = containerElt
        this.settings = settings
        this.locationType = containerElt.dataset.locationSearch ?? 'address'
        this.locationDept = this.containerElt.dataset.locationDept ?? null
        this.create = containerElt.dataset.locationSearchCreate ?? false
        this.minLength = containerElt.dataset.locationSearchMinLength ?? 3 // minimum query length before to load
        this.delay = containerElt.dataset.locationSearchDelay ?? 500 // number of milliseconds to wait before requesting options from the server or null
        this.limit = containerElt.dataset.locationSearchLimit ?? 5 // number elements into the list
        this.lat = containerElt.dataset.locationLat
        this.lon = containerElt.dataset.locationLon

        this.lat = this.lat !== '' ? this.lat : 48.85 // latitude
        this.lon = this.lon !== '' ? this.lon : 2.35 // longitude
        
        this.searchElt = containerElt.querySelector('[data-location-type="search"]')
        this.fullAddressElt = containerElt.querySelector('[data-location-type="full_address"]')
        this.addressElt = containerElt.querySelector('[data-location-type="address"]')
        this.cityElt = containerElt.querySelector('[data-location-type="city"]')
        this.zipcodeElt = containerElt.querySelector('[data-location-type="zipcode"]')
        this.locationIdElt = containerElt.querySelector('[data-location-type="locationId"]')
        this.latElt = containerElt.querySelector('[data-location-type="lat"]')
        this.lonElt = containerElt.querySelector('[data-location-type="lon"]')

        this.searchSelect = new TomSelect(this.searchElt, this.#getSettings())

        this.#init()
    }

    #init() {
        this.searchElt.addEventListener('change', () => {
            this.#updateLocationInputs(this.searchElt.value)
        })

        this.searchSelect.on('item_remove', () => {
            this.searchSelect.close()
        })
    }

    /**
     * Update the input fields.
     * 
     * @param {string} id
     */
     #updateLocationInputs(id) {
        if (!id) {
            return 
        }

        const result = this.searchSelect.options[id]

        if (this.containerElt.dataset.locationDept) {
            return
        }

        this.searchElt.value = result.label

        if (result.city && this.cityElt) {
            this.cityElt.value = result.city
        }

        if (this.zipcodeElt) {
            this.zipcodeElt.value = result.postcode
        }

        if (this.addressElt) {
            this.addressElt.value = result.name
        }

        if (this.locationIdElt) {
            this.locationIdElt.value = result.id
            this.lonElt.value = result.lon
            this.latElt.value = result.lat
        }
    }

    /**
     * @param {string} id 
     * @param {string} label 
     */
    refreshItem(id, label) {
        this.searchSelect.clear()

        if (label !== null) {
            this.searchSelect.addOption({id: id, label: label})
            this.searchSelect.addItem(id)
        }
    }

    /**
     * Get settings of TomSelect
     *
     * @returns {Object}
     */
    #getSettings() {
        const defaultSettings = {
            valueField: 'id',
            labelField: 'label',
            searchField: 'query',
            create: this.create,
            loadThrottle: this.delay, // number of milliseconds to wait before requesting options from the server or null
            closeAfterSelect: true,
            selectOnTab: true,
            maxItems: 1,
            delimiter: ';',
            addPrecedence: true,
            openOnFocus: false,
            
            shouldLoad: (query) => {
                return query.length >= this.minLength
            },
            load: (query, callback) => {
                fetch(this.#getUrl(query))
                    .then(response => response.json())
                    .then(json => {
                        callback(this.#getResults(json, query))
                    }).catch((error) => {
                        console.error(error)
                        callback()
                    })
            },
            render: this.#getRender(),
        }

        return {
            ...defaultSettings,
            ...this.settings,
        }
    }

    /**
     * @param {string} query 
     * @returns {string}
     */
     #getUrl(query) {
        query = query.replace(/(\s|\t){1,}/g, '+')
        const geo = `&lat=${this.lat}&lon=${this.lon}` // Get the geo priority with lat and lon
        const locationDept = this.containerElt.dataset.locationDept ?? null
        let url = this.url

        if (locationDept) {
            return 'https://geo.api.gouv.fr/communes?nom=' + query + '&codeDepartement=' + locationDept + '&limit=' + this.limit
        }

        if (this.locationType === 'city') {
            url = url + query + '&type=municipality' + '&autocomplete=1' + '&limit=' + this.limit
        }

        return url + query + geo + '&limit=' + this.limit
    }

    /**
     * Return data results from ajax request
     * 
     * @param {JSON} json 
     * @param {string} query 
     * @returns {array}
     */
    #getResults(json, query) {
        this.searchSelect.clearOptions()
        const results = []

        if (this.locationType === 'city' && this.locationDept) {
            json.forEach(feat => {
                results.push({
                    id: feat.nom,
                    context: feat.codesPostaux[0],
                    label: feat.nom,
                    postcode: feat.codesPostaux[0],
                    query: query
                })
            })

            return results
        }

        json.features.forEach(feat => {
            results.push({
                id: feat.properties.id,
                city: feat.properties.city,
                context: feat.properties.context,
                label: feat.properties.label,
                lat: feat.geometry.coordinates[1],
                lon: feat.geometry.coordinates[0],
                name: feat.properties.name,
                postcode: feat.properties.postcode,
                query: query
            })
        })

        return results
    }

    #getRender() {
        return {
            option: (item, escape) => {
                return `
                    <div class="d-flex py-1">
                        <div>
                            <span>${ escape(item.label) }</span>
                            <span class="small text-secondary">${ escape(item.context ?? '') }</span>
                        </div>
                    </div>`
            },
            no_results: (data, escape) => {
                return `<div class="no-results text-secondary small">Pas de résultat pour "${ escape(data.input) }"</div>'`
            },            
        }
    }
}