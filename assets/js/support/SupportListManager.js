import Loader from "../utils/loader";
import Ajax from "../utils/ajax";
import AlertMessage from "../utils/AlertMessage";

export default class SupportListManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader, 45)

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
        if (response.msg) {
            this.messageFlash = new AlertMessage(response.alert, response.msg)
        }

        switch (response.action) {
            case 'restore':
                this.deleteSupportTr(response.support)
                this.checkToRedirect(this.messageFlash.delay)
                break
        }
    }

    /**
     * @param {Object} support
     */
    deleteSupportTr(support) {
        document.querySelectorAll('table#table-supports tbody tr').forEach(trElt => {
            if ('support' in trElt.dataset && trElt.dataset.support === 'support-'+support.id) {
                trElt.remove()
                this.updateCounterSupports(-1)
            }
        })
    }

    /**
     * @param {number} value
     */
    updateCounterSupports(value) {
        const counterSupportElt = document.getElementById('count-supports')
        const calcul = parseInt(counterSupportElt.dataset.countSupports)+value

        counterSupportElt.textContent = counterSupportElt.textContent
            .replace(counterSupportElt.dataset.countSupports, calcul.toString())
        counterSupportElt.dataset.countSupports = calcul.toString()
    }

    /**
     * Redirects if there are no more lines.
     * @param {number} delay
     */
    checkToRedirect(delay) {
        if (document.querySelectorAll('table#table-supports tbody tr').length === 0) {
            setTimeout(() => {
                document.location.href = location.pathname
            }, delay * 1000)
        }
    }
}