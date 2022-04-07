import Loader from "../utils/loader";
import Ajax from "../utils/ajax";

export default class SupportListManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.init()
    }

    init() {
        document.querySelectorAll('table#table-supports tbody button[data-action="restore"]')
            .forEach(restoreBtn => restoreBtn
                .addEventListener('click', () => this.requestRestoreSupport(restoreBtn)))
    }


    /**
     * @param {HTMLLinkElement} restoreBtn
     */
    requestRestoreSupport(restoreBtn) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', restoreBtn.dataset.url, this.responseAjax.bind(this))
        }
    }

    responseAjax(response) {
        switch (response.action) {
            case 'restore':
                this.deleteSupportTr(response.support)
                break
        }
    }

    /**
     * @param {Object} support
     */
    deleteSupportTr(support) {
        const rowElt = document.getElementById('support-'+support.id)
        rowElt.remove()

        this.updateCounterSupports(-1)
    }

    /**
     * @param {number} value
     */
    updateCounterSupports(value) {
        const counterSupportElt = document.getElementById('count-supports')
        const calcul = parseInt(counterSupportElt.dataset.countSupports)+value

        counterSupportElt.textContent = counterSupportElt.textContent.replace(counterSupportElt.dataset.countSupports, calcul.toString())
        counterSupportElt.dataset.countSupports = calcul.toString()
    }
}