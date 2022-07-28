
import TomSelect from 'tom-select'
import Loader from './utils/loader'

/**
 * Dynamic search person with TomSelect and ajax loading.
 */
export default class PersonSearcher {
    /**
     * @param {string} selector 
     * @param {number} minSearchLength 
     */
    constructor(selector, minLength = 3) {
        this.url = '/search/person/'
        this.showPersonUrl = '/person/'
        this.searchPersonUrl = '/people/'

        this.searchElt = document.querySelector(selector)
        this.delay = this.searchElt.dataset.searchDelay ?? 500 // number of milliseconds to wait before requesting options from the server or null
        this.minLength = minLength // minimum query length before to load

        this.searchSelect = new TomSelect(this.searchElt, this.#getSettings())
        this.loader = new Loader()

        this.#init()
    }

    #init() {       
        document.getElementById(this.searchElt.id + '-ts-control').addEventListener('input', (e) => this.#cleanInput(e))

        this.searchSelect.on('item_add', (value, item) => this.#onAddItem(value, item))
    }

    /**
     * Clean the input value after paste.
     * 
     * @param {Event} e 
     */
    #cleanInput(e) {
        if (e.inputType === 'insertFromPaste') {
            e.target.value = e.target.value.replace(/(\s|\t){1,}/g, ' ')
        }
    }

    /**
     * Submit form or go to person page after to add item.
     * 
     * @param {string} value 
     * @param {HTMLElement} item 
     */
    #onAddItem(value, item) {
        this.loader.on()

        value = this.#cleanQuery(value)

        if (!isNaN(value) && value !== item.textContent) {
            return location.href = this.showPersonUrl + value
        }

        this.searchElt.value = value
        document.getElementById('search_person_form').submit()
    }

    /**
     * Get settings of TomSelect
     *
     * @returns {Object}
     */
    #getSettings() {
        return {
            valueField: 'id',
            labelField: 'fullname',
            searchField: 'query',
            create: true,
            loadThrottle: this.delay, // number of milliseconds to wait before requesting options from the server or null
            closeAfterSelect: true,
            selectOnTab: true,
            maxItems: 1,
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
    }

    /**
     * @param {string} query 
     * @returns {string}
     */
     #getUrl(query) {
        return this.url + this.#cleanQuery(query)
    }
    
    /**
     * @param {string} query 
     * @returns {string}
     */
    #cleanQuery(query) {
        return query
            .replace(/(\s|\t|\+){1,}/g, '+')
            .replaceAll('/', '-')
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
        json.people.forEach(person => {
            person['query'] = query
        })

        return json.people
    }

    #getRender() {
        return {
            option: (person, escape) => {
                return `
                    <div class="d-flex py-1">
                        <div>
                            <span class="small">${ escape(person.fullname) }</span>
                            ${person.birthdate ? '<span class="small text-secondary">(' + escape(person.birthdate) + ')</span>' : ''}
                        </div>
                    </div>`
            },
            no_results: (data, escape) => {
                return `<div class="no-results text-secondary small">Pas de résultat pour "${ escape(data.input) }"</div>'`
            },   
            option_create: function(data, escape) {
                return '<div class="create">Rechercher <strong>"' + escape(data.input) + '"</strong>&hellip;</div>';
            },       
        }
    }
}