import Loader from "../utils/loader";
import Ajax from "../utils/ajax";
import AlertMessage from "../utils/AlertMessage";
import RedirectChecker from '../utils/RedirectChecker'

export default class SupportListManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader, 45)

        this.init()
    }

    init() {
        document.querySelectorAll('#table_supports tbody button[data-action="restore"]')
            .forEach(restoreBtn => restoreBtn
                .addEventListener('click', () => this.requestRestoreSupport(restoreBtn)))
    }

    /**
     * @param {HTMLLinkElement} restoreBtn
     */
    requestRestoreSupport(restoreBtn) {
        if (this.loader.isActive() === false) {
            this.loader.on()

            this.ajax.send('GET', restoreBtn.dataset.pathRestore, (resp) => this.responseAjax(resp))
        }
    }

    responseAjax(response) {
        switch (response.action) {
            case 'restore':
                this.deleteSupportTr(response.support)
                new RedirectChecker(
                    document.querySelectorAll('#table_supports tbody tr').length === 0,
                    null, null, 'Vous allez être redirigé vers la liste des suivis...'
                )
                break
        }

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }
    }

    /**
     * @param {Object} support
     */
    deleteSupportTr(support) {
        document.querySelectorAll('#table_supports tbody tr').forEach(trElt => {
            if ('support' in trElt.dataset && trElt.dataset.supportId === support.id) {
                trElt.remove()
                this.updateCounterSupports(-1)
            }
        })
    }

    /**
     * @param {number} value
     */
    updateCounterSupports(value) {
        const counterSupportElt = document.getElementById('counter_supports')
        const calcul = parseInt(counterSupportElt.dataset.countSupports)+value

        counterSupportElt.textContent = counterSupportElt.textContent
            .replace(counterSupportElt.dataset.countSupports, calcul.toString())
        counterSupportElt.dataset.countSupports = calcul.toString()
    }
}