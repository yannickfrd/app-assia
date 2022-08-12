import AbstractManager from '../AbstractManager'
import PaymentForm from "./PaymentForm"
import AlertMessage from '../utils/AlertMessage'

export default class PaymentManager extends AbstractManager {

    constructor() {
        super('payment')

        this.form = new PaymentForm(this)

        // Additionnal requests
        this.requestExportPdf = (id) => this.request('export-pdf', id)
        this.requestSendEmail = (id) => {
            if (window.confirm('Confirmer l\'envoi du reçu par email au suivi ?')) {
                this.request('send-email', id)
            }
        }
        
        this.sumToPayAmtElt = document.querySelector('td[data-sum="sumToPayAmt"]')
        this.sumPaidAmtElt = document.querySelector('td[data-sum="sumPaidAmt"]')
        this.sumStillToPayAmtElt = document.querySelector('td[data-sum="sumStillToPayAmt"]')
        
        this.#calculateSumAmts()
    }

    /**
     * Actions after Ajax response.
     * 
     * @param {Object} response 
     */
     responseAjax(response) {
        const payment = response.payment

        if (payment !== undefined) {
            this.checkActions(response, payment)
        }

        switch (response.action) {
            case 'download':
                this.#updatePaymentPictoPdf()
                return this.getFile(response.data)
            case 'send_receipt':
                return this.#updatePaymentPictoMail(payment)
        }

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        this.#calculateSumAmts()

        this.objectModal?.hide()
    }

    /**
     * Addionnal event listeners to the object element.
     * 
     * @param {HTMLTableRowElement} trElt 
     */
     extraListenersToElt(trElt) {
        const id = trElt.dataset.paymentId
        // Export PDF
        trElt.querySelector('[data-action="export_pdf"]')
            ?.addEventListener('click', () => this.requestExportPdf(id))
        // Send Email with PDF
        trElt.querySelector('[data-action="send_email"]')
            ?.addEventListener('click', () => this.requestSendEmail(id))
        
    }

    /**
     * Donne la somme des montants.
     * 
     * @param {HTMLElement} elts 
     * 
     * @return {number|string}
     */
    #getSumAmts(elts) {
        let sumAmts = 0
        elts.forEach(elt => {
            let amount = parseFloat(elt.textContent.replace(/(\s|\xc2\xa0){1,}/g, '').replace(',', '.'))
            if (!isNaN(amount)) {
                sumAmts += amount
            }
        })
        if (!isNaN(sumAmts)) {
            return sumAmts
        }
        return 'Err.'
    }

    /**
     * Change la couleur du texte d'un élément on fonction de la valeur d'un nombre.
     * 
     * @param {HTMLElement} elt 
     * @param {string} value 
     */
    changeTextColor(elt, value) {
        if (parseFloat(value) > 0) {
            elt.classList.remove('text-success')
            elt.classList.add('text-danger')
        } else {
            elt.classList.remove('text-danger')
            elt.classList.add('text-success')
        }
    }

    #updatePaymentPictoPdf() {
        const pictoElt = this.findElt(this.objectId).querySelector('[data-action="export_pdf"] i')
        pictoElt.classList.replace('text-secondary', 'text-success')
    }

    /**
     * @param {Object} payment
     */
    #updatePaymentPictoMail(payment) {       
        const pictoElt = this.findElt(payment.id).querySelector('[data-action="send_email"] i')
        pictoElt.classList.replace('text-secondary', 'text-success')
    }

    /**
     * Calculate the sum of all amounts in header or footer.
     */
    #calculateSumAmts() {
        const sumToPayAmt = this.#getSumAmts(document.querySelectorAll('td[data-object-key="toPayAmt"]'))
        this.sumToPayAmtElt.textContent = sumToPayAmt.toLocaleString() + ' €'
        const sumPaidAmt = this.#getSumAmts(document.querySelectorAll('td[data-object-key="paidAmt"]'))
        this.sumPaidAmtElt.textContent = sumPaidAmt.toLocaleString() + ' €'

        const stillToPayAmtElts = document.querySelectorAll('td[data-object-key="stillToPayAmt"]')
        stillToPayAmtElts.forEach(elt => {
            this.changeTextColor(elt, elt.textContent)
        })

        const sumStillToPayAmt = (sumToPayAmt - sumPaidAmt)
        this.sumStillToPayAmtElt.textContent = sumStillToPayAmt.toLocaleString() + ' €'
        this.changeTextColor(this.sumStillToPayAmtElt, sumStillToPayAmt)
    }
}