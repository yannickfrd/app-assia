import Loader from '../utils/loader'
import Ajax from '../utils/ajax'
import SiSiaoLogin from '../siSiao/siSiaoLogin'
import AlertMessage from '../utils/AlertMessage'
import { Modal } from 'bootstrap'
import  '../utils/maskNumber'

/**
 * Recherche instannée Ajax.
 */
export default class SearchPerson {

    constructor(lengthSearch = 3, time = 500) {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.siSiaoLogin = new SiSiaoLogin()
        this.formElt = document.querySelector('#people_search')
        this.formModal = document.querySelector('.modal-content form')
        this.searchBtnElt = document.getElementById('search')
        this.lastnameInputElt = document.getElementById('lastname')
        this.firstnameInputElt = document.getElementById('firstname')
        this.birthdateInputElt = document.getElementById('birthdate')
        this.siSiaoIdInputElt = document.getElementById('siSiaoId')
        this.tableElt = document.querySelector('div.table-responsive')
        this.listResultElt = document.getElementById('list-result-people')
        this.groupId = this.listResultElt.dataset.groupId
        this.helperSearchElt = document.querySelector('.js-helper-search')
        this.createPersonBtnElt = document.querySelector('[data-action="create-person"]')
        this.lengthSearch = lengthSearch
        this.time = time
        this.countdownID = null
        this.paramsToString = null
        this.helperSearch = this.helperSearchElt.textContent
        this.valueSearch = ''
        
        this.siSiaoSearchCheckboxElt = document.getElementById('siSiaoSearch')
        
        if (this.siSiaoSearchCheckboxElt) {
            this.siSiaoGroupModal = new Modal(document.getElementById('modal-si-siao-group'))
            this.importSiSiaoGroupAElt = document.querySelector('a[data-action="import-si-siao-group"]');
        }
        
        this.init()
    }
    
    init() {
        [this.siSiaoIdInputElt, this.lastnameInputElt, this.firstnameInputElt].forEach(elt =>
            elt.addEventListener('keyup', () => this.timer())
        );

        this.birthdateInputElt.addEventListener('change', () => this.checkDate(this.birthdateInputElt))
        this.searchBtnElt.addEventListener('click', e => this.onClickBtnElt(e))
        this.createPersonBtnElt.addEventListener('click', () => this.setParams())

        if (this.siSiaoSearchCheckboxElt) {
            this.siSiaoLogin.init('siSiaoSearch')
            
            this.importSiSiaoGroupAElt.addEventListener('click', e => {
                if (this.loader.isActive()) {
                    return e.preventDefault()
                }
                this.loader.on()
            })
        }
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
        const newValueSearch = this.siSiaoIdInputElt.value + this.lastnameInputElt.value + this.firstnameInputElt.value
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
            this.noParams()
        }
        if (!this.loader.isActive()) {
            this.sendRequest()
        } else {
            setTimeout(() => this.loader.off(), 1000)
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
        if (this.siSiaoSearchCheckboxElt && this.siSiaoSearchCheckboxElt.checked && this.siSiaoLogin.isConnected) {
            if (0 === this.siSiaoIdInputElt.value.length) {
                return new AlertMessage('danger', "L'ID groupe est obligatoire pour effectuer une recherche via le SI-SIAO.")
            }
            const url = '/api-sisiao/show-group/'+ this.siSiaoIdInputElt.value
            return this.ajax.send('GET', url, this.responseShowGroup.bind(this))
        }
        
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
        this.createPersonBtnElt.classList.remove('d-none')

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
            `<td scope="row" class="align-middle text-center"></td>
            <td class="align-middle">${this.getLastname(person)}</td>
            <td class="align-middle text-capitalize">${person.firstname}</td>
            <td class="align-middle">${person.birthdate}</td>
            <td class="align-middle">${person.age} an${person.age > 1 ? 's' : '' }</td>
            <td class="align-middle">
                <span class="fas fa-${person.gender == 1 ? 'female' : 'male'} fa-2x text-dark" data-bs-placement="bottom" 
                title="${person.gender == 1 ? 'Femme' : 'Homme'}"></span>
            </td>`
         
        trElt.querySelector('td').innerHTML = this.addBtnElt(person)

        if (this.groupId) {
            const aElt = trElt.querySelector('a')
            aElt.addEventListener('click', e => {
                e.preventDefault()
                this.formModal.action = aElt.href
            })
        }
        
        const btnElt = trElt.querySelector('button[data-action="show-group"]')
        if (btnElt) {
            btnElt.addEventListener('click', e => {
                this.ajax.send('GET', '/api-sisiao/show-group/' + btnElt.dataset.id, this.responseShowGroup.bind(this))
            })            
        }

        this.listResultElt.appendChild(trElt)
    }

    /**
     * Donne la réponse à la requête Ajax.
     * @param {Object} data 
     */
    responseShowGroup(data) {
        if ('warning' === data.alert) {
            return this.noResults()
        }
        
        document.querySelector('#modal-si-siao-group .modal-body').innerHTML = data.html.content
        this.siSiaoGroupModal.show()

        this.importSiSiaoGroupAElt.href = this.importSiSiaoGroupAElt.dataset.url.replace('__id__', data.idGroup)
        this.loader.off()
    }

    /**
     * Ajoute un élément à la liste des résultats.
     * @param {Object} person 
     */
    getLastname(person) {
        if (person.id) {
            return `<a href="/person/${person.id}" class="text-dark text-uppercase fw-bold">
                ${person.lastname}${person.usename ? ' ('+ person.usename + ')' : ''}</a`
        }

        return person.lastname + '<span class="ms-1">(<i class="fas fa-map-marker-alt me-1"></i>' + person.deptCode + ')</span>'
    }

    /**
     * Ajoute l'élément <Bouton>.
     * @param {Object} person 
     */
    addBtnElt(person) {
        if (this.groupId) {
            return `<a href="/group/${this.groupId}/add_person/${person.id}" class="js-add-person shadow" 
                        data-bs-toggle="modal" data-bs-target="#modal-block" data-bs-placement="bottom" title="Ajouter la personne au groupe">
                        <span class="fas fa-plus-square text-dark fa-2x"></span>
                    </a>`
        }

        if (person.id) {
            return `<a href="/person/${person.id}" class="btn btn-primary btn-sm shadow"
                        data-bs-placement="bottom" title="Voir la fiche de la personne"><span class="fas fa-eye"></span>
                    </a>`
        }

        return `<button data-action="show-group" data-id="${person.idFiche}" class="btn bg-primary btn-sm shadow"
                    data-bs-placement="bottom" title="Voir la fiche groupe SI-SIAO de cette personne"><i class="fas fa-eye"></i>
                </button>`
    }

    /**
     * Crée les paramètres en GET dans l'URL.
     */
    setParams() {
        let params = ''
        this.formElt.querySelectorAll('input').forEach(input => {
            params += `${input.id}=${input.value}&`
        })
        
        const prefix = this.createPersonBtnElt.href.includes('?') ? '&' : '?'
        this.createPersonBtnElt.href += prefix + params
    }
}