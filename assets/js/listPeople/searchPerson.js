import Loader from '../utils/loader'
import Ajax from '../utils/ajax'

/**
 * Recherche instannée Ajax.
 */
export default class SearchPerson {

    constructor(lengthSearch = 3, time = 500) {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        
        this.formElt = document.querySelector('#people_search')
        this.formModal = document.querySelector('.modal-content form')
        this.searchBtnElt = document.getElementById('search')
        this.lastnameInputElt = document.getElementById('lastname')
        this.firstnameInputElt = document.getElementById('firstname')
        this.birthdateInputElt = document.getElementById('birthdate')
        this.tableElt = document.querySelector('div.table-responsive')
        this.listResultElt = document.getElementById('list-result-people')
        this.groupId = this.listResultElt.getAttribute('data-group-id')
        this.helperSearchElt = document.querySelector('.js-helper-search')
        this.anchorCreatePersonElt = document.querySelector('.js-create-person')
        this.themeColor = document.getElementById('header').getAttribute('data-color')
        this.lengthSearch = lengthSearch
        this.time = time
        this.countdownID = null
        this.paramsToString = null
        this.helperSearch = this.helperSearchElt.textContent
        this.valueSearch = ''

        this.init()
    }
    
    init() {
        this.lastnameInputElt.addEventListener('keyup', () => this.timer())
        this.firstnameInputElt.addEventListener('keyup', () => this.timer())
        this.birthdateInputElt.addEventListener('change', () => this.checkDate(this.birthdateInputElt))
        this.searchBtnElt.addEventListener('click', e => this.onClickBtnElt(e))
    }

    /**
     * Timer avant de lancer la requête Ajax.
     */
    timer() {
        clearInterval(this.countdownID)
        this.countdownID = setTimeout(this.count.bind(this), this.time)
    }

    /**
     * Compte le nombre de caratères saisis et lance la requête Ajax<.
     */
    count() {
        const newValueSearch = this.lastnameInputElt.value + this.firstnameInputElt.value
        if (this.valueSearch != newValueSearch && newValueSearch.length >= this.lengthSearch) {
            this.valueSearch = newValueSearch
            this.sendRequest()
        }
    }

    /**
     * Au clic sur le bouton 'Search'.
     * @param {Event} e 
     */
    onClickBtnElt(e) {
        e.preventDefault()
        if (this.checkParamsInForm() === false) {
            return this.noParams()
        }
        if (!this.loader.isActive()) {
            this.sendRequest()
        }
    }

    checkParamsInForm() {
        let data = false
        this.formElt.querySelectorAll('input').forEach(inputElt => {
            if (inputElt.value) {
                data = true
            }
        })
        return data
    }


    /**
     * Vérifie la validité de la date saisie.
     * @param {HTMLInputElement} inputElt 
     */
    checkDate(inputElt) {
        const intervalWithNow = (new Date() - new Date(inputElt.value)) / (24 * 3600 * 1000)
        if (intervalWithNow > 0 && intervalWithNow < (365 * 99)) {
            this.sendRequest()
        } 
    }

    /**
     * Envoie la requête Ajax.
     */
    sendRequest() {
        const formData = new FormData(this.formElt)
        const paramsToString = new URLSearchParams(formData).toString()
        if (paramsToString != this.paramsToString) {
            this.ajax.send('POST', '/people/search', this.response.bind(this), new FormData(this.formElt))
            this.loader.on()
            this.paramsToString = paramsToString
        }
    }

    /**
     * Donne la réponse à la requête Ajax.
     * @param {Object} data 
     */
    response(data) {
        this.listResultElt.innerHTML = ''
        if (data.people.length > 0) {
            this.showResults(data.people)
        } else {
            this.noResults()
        }
        this.displayCreateNewPerson()
        this.loader.off()
    }

    noResults() {
        this.helperSearchElt.textContent = 'Aucun résultat.'
        this.tableElt.classList.add('d-none')
    }

    noParams() {
        this.helperSearchElt.textContent = this.helperSearch
        this.tableElt.classList.add('d-none')
    }

    /**
     * Affiche les résultats.
     * @param {Array} people 
     */
    showResults(people) {
        this.helperSearchElt.textContent = (people.length >= 20 ? 'Plus de ' : '') + people.length + ' résultat' + (people.length > 1  ? 's' : '') + '.'
        this.tableElt.classList.remove('d-none')
        people.forEach(person => {
            this.addItem(person)
        })
    }

    /**
     * Ajoute un élément à la liste des résultats.
     * @param {Object} person 
     */
    addItem(person) {
        const trElt = document.createElement('tr')
        trElt.innerHTML = 
            `<td scope="row" class="align-middle text-center">
            </td>
            <td class="align-middle"><a href="/person/${person.id}" class="text-dark text-uppercase font-weight-bold">
                ${person.lastname}${person.usename ? ' ('+person.usename+')' : ''}</a
            </td>
            <td class="align-middle text-capitalize">${person.firstname}</td>
            <td class="align-middle">${person.birthdate}</td>
            <td class="align-middle">${person.age} an${person.age > 1 ? 's' : '' }</td>
            <td class="align-middle">
                <span class="fas fa-${person.gender == 1 ? 'female' : 'male'} fa-2x text-dark" data-placement="bottom" 
                title="${person.gender == 1 ? 'Femme' : 'Homme'}"></span>
            </td>`
         
        trElt.querySelector('td').innerHTML = this.addAnchorElt(person)

        if (this.groupId) {
            const aElt = trElt.querySelector('a')
            aElt.addEventListener('click', e => {
                e.preventDefault()
                this.formModal.action = aElt.href
            })
        }
        
        this.listResultElt.appendChild(trElt)
    }

    /**
     * Ajoute l'élément <Bouton>.
     * @param {Object} person 
     */
    addAnchorElt(person) {
        if (this.groupId) {
            return `<a href="/group/${this.groupId}/add/person/${person.id}" class="js-add-person shadow" 
                        data-toggle="modal" data-target="#modal-block" data-placement="bottom" title="Ajouter la personne au groupe">
                        <span class="fas fa-plus-square text-dark fa-2x"></span>
                    </a>`
        }
        return `<a href="/person/${person.id}" class="btn btn-${this.themeColor} btn-sm shadow"
                    data-placement="bottom" title="Voir la fiche de la personne"><span class="fas fa-eye"></span>
                </a>`
    }

    /**
     * Affiche le lien de création d'une nouvelle personne.
     */
    displayCreateNewPerson() {
        this.anchorCreatePersonElt.addEventListener('click', this.setParams.bind(this))
        this.anchorCreatePersonElt.classList.remove('d-none')
    }

    /**
     * Crée les paramètres en GET dans l'URL.
     */
    setParams() {
        let params = ''
        document.querySelectorAll('input').forEach(input => {
            if (input.id != 'search') {
                const key = input.id
                params += key + '=' + input.value + '&'
            }
        })
        this.anchorCreatePersonElt.href = this.anchorCreatePersonElt.href + '?' + params
    }
}