import SelectManager from './form/SelectManager'
import Loader from './loader'
import AlertMessage from './AlertMessage'

/**
 * Classe pour les différents pages de recherche.
 */
export default class SearchManager {

    constructor(formId) {
        this.loader = new Loader()
        this.formSearch = document.getElementById(formId)
        this.inputElts = this.formSearch.querySelectorAll('input')
        this.checkboxElts = this.formSearch.querySelectorAll('input[type="checkbox"]')
        this.selectElts = this.formSearch.querySelectorAll('select')
        this.resultsElt = document.getElementById('results')
        this.btnSearchElt = document.getElementById('search')
        this.btnExportElt = document.getElementById('js-btn-export')
        this.btnClearElt = this.formSearch.querySelector('button[type="reset"]')
        this.firstInput = this.formSearch.querySelector('input')
        this.selectManagers = []

        this.init()
    }

    init() {
        this.formSearch.querySelectorAll('select[multiple]').forEach(selectElt => {
            this.selectManagers.push(new SelectManager(selectElt))
        })

        if (this.btnSearchElt) {
            this.btnSearchElt.addEventListener('click', e => {
                this.loader.inLoading = false
                if (this.loader.isActive()) {
                        e.preventDefault()
                    }
                this.loader.on()
            })
        }

        this.btnClearElt.addEventListener('click', e => {
            this.loader.off()
            e.preventDefault()
            this.clearSearch()
        })

        if (this.btnExportElt) {
            this.btnExportElt.addEventListener('click', e => {
                if (this.loader.inLoading) {
                    e.preventDefault()
                } else {
                    new AlertMessage('success', 'L\'export est en cours de préparation. Merci de patienter...', 10)
                    this.loader.inLoading = true
                    setTimeout(() => {
                        this.loader.inLoading = false
                    }, 10 * 1000)
                }
            })
        }
    }

    /**
     * Efface les données du formulaire de recherche au clic/
     */
    clearSearch() {
        this.inputElts.forEach(inputElt => {
            if (!inputElt.dataset.noReset) {
                inputElt.value = null
            }

        })

        this.selectElts.forEach(selectElt => {
            if (!selectElt.dataset.noReset) {
                selectElt.value = ''
            }
        })

        this.checkboxElts.forEach(checkboxElt => {
            if (!checkboxElt.dataset.noReset) {
                checkboxElt.removeAttribute('checked')
                checkboxElt.value = '0'
            }
        })

        this.selectManagers.forEach(selectManager => {
            selectManager.clearItems()
        })

        if (this.resultsElt) {
            this.resultsElt.textContent = ''
        }

        if (this.firstInput) {
            this.firstInput.focus()
        }
    }
}
