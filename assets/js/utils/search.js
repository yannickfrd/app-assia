import Loader from './loader'
import MessageFlash from './messageFlash'

/**
 * Classe pour les différents pages de recherche.
 */
export default class Search {

    constructor(formId) {
        this.loader = new Loader();
        this.formSearch = document.getElementById(formId)
        this.inputElts = this.formSearch.getElementsByTagName('input')
        this.checkboxElts = this.formSearch.querySelectorAll('input[type="checkbox"]')
        this.selectElts = this.formSearch.getElementsByTagName('select')
        this.resultsElt = document.getElementById('results')
        this.btnSearchElt = document.getElementById('search')
        this.btnExportElt = document.getElementById('js-btn-export')
        this.btnClearElt = this.formSearch.querySelector('button[type="reset"]')
        this.firstInput = this.formSearch.querySelector('input')
        
        this.init()
    }

    init() {
        if (this.btnSearchElt) {
            this.btnSearchElt.addEventListener('click', e => {
            this.loader.inLoading = false
            if (this.loader.isActive()) {
                e.preventDefault()
            }
            this.loader.on(); 
        })   
        }


        this.btnClearElt.addEventListener('click', e => {
            this.loader.off(); 
            e.preventDefault()
            this.clearSearch()
        })

        if (this.btnExportElt) {
            this.btnExportElt.addEventListener('click', e => {
                if (this.loader.inLoading) {
                    e.preventDefault()
                } else {
                    new MessageFlash('success', 'L\'export est en cours de préparation. Merci de patienter...', 10);
                    this.loader.inLoading = true
                }
            })
        }
    }

    /**
     * Efface les données du formulaire de recherche au clic/
     */
    clearSearch() {
        this.inputElts.forEach(inputElt => {
            inputElt.value = null
        })
        this.checkboxElts.forEach(checkboxElt => {
            checkboxElt.removeAttribute('checked')
            checkboxElt.value = '0'
        })
        this.selectElts.forEach(selectElt => {
            selectElt.querySelectorAll('option').forEach(optionElt => {
                optionElt.removeAttribute('selected')
                optionElt.selected = ''
            })
        })

        this.formSearch.querySelectorAll('.select2-container').forEach(containerElt => {
            const removeElts = containerElt.querySelectorAll('.select2-selection__choice__remove')
            removeElts.forEach(removeElt => {
                removeElt.click()
            })
            if (removeElts.length > 0) {
                containerElt.querySelector('input').click()
            }
        })

        if (this.resultsElt) {
            this.resultsElt.textContent = ''
        }

        if (this.firstInput) {
            this.firstInput.focus()
        }
    }
}