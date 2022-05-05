import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import FormValidator from '../utils/form/formValidator'
import {Modal} from 'bootstrap'
import PaymentForm from "./PaymentForm";

export default class PaymentManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.formValidator = new FormValidator()
        this.paymentForm = new PaymentForm(this)

        this.paymentModalElt = new Modal(document.getElementById('payment-modal'))

        this.btnNewElt = document.querySelector('button[data-action="new_payment"]')

        this.confirmBtnElt = document.getElementById('modal-confirm')
        document.querySelector('#modal-block div.modal-content').classList.add('bg-light')

        this.sumToPayAmtElt = document.querySelector('td[data-payment="sumToPayAmt"]')
        this.sumPaidAmtElt = document.querySelector('td[data-payment="sumPaidAmt"]')
        this.sumStillToPayAmtElt = document.querySelector('td[data-payment="sumStillToPayAmt"]')

        this.themeColor = document.getElementById('header').dataset.color
        this.countPaymentsElt = document.getElementById('count-payments')
        this.nbTotalPaymentsElt = document.getElementById('nb-total-payments')

        this.now = new Date()
        this.init()
    }

    init() {
        this.btnNewElt.addEventListener('click', () => {
            if (this.loader.isActive() === false) {
                this.newPayment()
            }
        })

        document.querySelectorAll('table#table-payments tbody tr button[data-action="show"]')
            .forEach(showBtnElt => showBtnElt
                .addEventListener('click', () => this.requestShowPayment(showBtnElt)))

        document.querySelectorAll('table#table-payments tbody tr button[data-action="delete"]')
            .forEach(btnDeleteElt => btnDeleteElt
                .addEventListener('click', () => this.confirmBtnElt.dataset.url = btnDeleteElt.dataset.url))

        document.querySelectorAll('button[data-action="restore"]').forEach(restoreBtn => restoreBtn
            .addEventListener('click', () => this.requestRestorePayment(restoreBtn)))

        this.confirmBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.confirmBtnElt.dataset.url, this.responseAjax.bind(this))
        })

        this.calculateSumAmts()
    }

    /**
     * @param {HTMLLinkElement} restoreBtn
     */
    requestRestorePayment(restoreBtn) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', restoreBtn.dataset.url, this.responseAjax.bind(this))
        }
    }

    /**
     * Calcul la somme de tous les montants pour le footer du tableau.
     */
    calculateSumAmts() {
        const sumToPayAmt = this.getSumAmts(document.querySelectorAll('td[data-payment="toPayAmt"]'))
        this.sumToPayAmtElt.textContent = sumToPayAmt.toLocaleString() + ' €'
        const sumPaidAmt = this.getSumAmts(document.querySelectorAll('td[data-payment="paidAmt"]'))
        this.sumPaidAmtElt.textContent = sumPaidAmt.toLocaleString() + ' €'

        const stillToPayAmtElts = document.querySelectorAll('td[data-payment="stillToPayAmt"]')
        stillToPayAmtElts.forEach(elt => {
            this.changeTextColor(elt, elt.textContent)
        })

        const sumStillToPayAmt = (sumToPayAmt - sumPaidAmt)
        this.sumStillToPayAmtElt.textContent = sumStillToPayAmt.toLocaleString() + ' €'
        this.changeTextColor(this.sumStillToPayAmtElt, sumStillToPayAmt)
    }

    /**
     * Displays a blank modal form.
     */
    newPayment() {
        this.paymentForm.newPayment()
        this.paymentModalElt.show()
    }

    /**
     * Permet de voir un paiement
     * @param {HTMLButtonElement} btnShowElt
     */
    requestShowPayment(btnShowElt) {
        if (!this.loader.isActive()) {
            this.paymentForm.formPaymentElt.action = this.paymentForm.formPaymentElt.dataset.url
                .replace('__id__', btnShowElt.dataset.id)

            this.loader.on()

            this.ajax.send('GET', btnShowElt.dataset.url, this.responseAjax.bind(this))
        }
    }

    /**
     * Réponse du serveur.
     * @param {Object} response 
     */
    responseAjax(response) {
        const payment = response.payment

        switch (response.action) {
            case 'show':
                this.showPayment(payment)
                break
            case 'create':
                this.createPayment(payment)
                break
            case 'update':
                this.updatePayment(payment)
                break
            case 'delete':
                this.deletedPaymentTr(payment)
                break
            case 'restore':
                this.deletedPaymentTr(payment)

                this.messageFlash = new MessageFlash(response.alert, response.msg)
                this.checkToRedirect(this.messageFlash.delay)
                break
        }

        if (!this.loader.isActive()) {
            this.loader.off()
            this.paymentModalElt.hide()

            if (response.msg && !this.messageFlash) {
                new MessageFlash(response.alert, response.msg)
            }
        }
        this.calculateSumAmts()
    }

    /**
     * Show payment in form.
     * @param {Object} payment
     */
    showPayment(payment) {
        this.paymentForm.showPayment(payment)
        this.paymentModalElt.show()
    }

    /**
     * Crée la ligne de la nouvelle redevance dans le tableau.
     * @param {Array} payment 
     */
    createPayment(payment) {
        this.paymentForm.formPaymentElt.action = this.paymentForm.formPaymentElt.dataset.url.replace('__id__', payment.id)
        this.paymentForm.editBtnElts.forEach(elt => elt.classList.remove('d-none'))

        this.paymentForm.saveBtnElt.querySelector('span').textContent = 'Mettre à jour'

        const paymentElt = document.createElement('tr')
        paymentElt.className = 'js-payment'
        paymentElt.innerHTML = this.getPrototypePayment(payment)

        const containerPaymentsElt = document.getElementById('container-payments')
        containerPaymentsElt.insertBefore(paymentElt, containerPaymentsElt.firstChild)
        this.updateCounts(1)

        this.calculateSumAmts()

        const showBtnElt = paymentElt.querySelector('button[data-action="show"]')
        showBtnElt.addEventListener('click', () => this.requestShowPayment(showBtnElt))

        const btnDeleteElt = paymentElt.querySelector('button[data-action="delete"]')
        btnDeleteElt.addEventListener('click', () => this.confirmBtnElt.dataset.url = btnDeleteElt.dataset.url)
    }

    /**
     * Met à jour la ligne du tableau correspondant au paiement.
     * @param {Object} payment 
     */
    updatePayment(payment) {
        const trElt = document.getElementById('payment-' + payment.id)
        trElt.querySelector('td[data-payment="type"]').textContent = payment.typeToString + (payment.type === 11 && payment.returnAmt !== null ? ' (' + this.formatMoney(payment.returnAmt) + ')' : '')
        trElt.querySelector('td[data-payment="startDate"]').textContent = this.formatDatetime(payment.startDate, 'date') + ' - ' + this.formatDatetime(payment.endDate, 'date')
        trElt.querySelector('td[data-payment="toPayAmt"]').textContent = this.formatMoney(payment.toPayAmt)
        trElt.querySelector('td[data-payment="paidAmt"]').textContent = payment.paidAmt ?? this.formatMoney(payment.paidAmt)
        trElt.querySelector('td[data-payment="stillToPayAmt"]').textContent = this.formatMoney(this.roundAmount(payment.stillToPayAmt))
        trElt.querySelector('td[data-payment="paymentDate"]').textContent = this.formatDatetime(payment.paymentDate, 'date')
        trElt.querySelector('td[data-payment="paymentType"]').textContent = payment.paymentTypeToString ?? ''
        trElt.querySelector('td[data-payment="comment"]').textContent = (payment.noContrib ? 'PAF à zéro (' + payment.noContribReasonToString + ') ' : '')
            + this.sliceComment((payment.comment ?? '') + " \n" + (payment.commentExport ?? ''))
        this.calculateSumAmts()
    }

    /**
     * Crée la ligne du paiement.
     * @param {Object} payment 
     */
    getPrototypePayment(payment) {
        return `
            <td class="align-middle text-center">
                <button class="btn btn-${this.themeColor} btn-sm shadow" data-action="show" data-id="${payment.id}" 
                    data-url="/payment/${payment.id}/show" data-toggle="tooltip" 
                    data-placement="bottom" title="Voir l'enregistrement"><span class="fas fa-eye"></span>
                </button>
            </td>
            <td class="align-middle" data-payment="type">${payment.typeToString}<br/>
                <span class="text-secondary">${payment.type === 11 ? ' (' + this.formatMoney(payment.returnAmt) + ')' : '' }</span>
            </td>
            <td class="align-middle" data-payment="startDate">${this.formatDatetime(payment.startDate, 'date') + ' - ' + this.formatDatetime(payment.endDate, 'date')}</td>
            <td class="align-middle text-right" data-payment="toPayAmt">${this.formatMoney(payment.toPayAmt)}</td>
            <td class="align-middle text-right" data-payment="paidAmt">${this.formatMoney(payment.paidAmt)}</td>
            <td class="align-middle text-right" data-payment="stillToPayAmt">${this.formatMoney(this.roundAmount(payment.stillToPayAmt))}</td>
            <td class="align-middle text-center" data-payment="paymentDate">${this.formatDatetime(payment.paymentDate, 'date')}</td>
            <td class="align-middle" data-payment="paymentType">${payment.paymentType ? payment.paymentTypeToString : ''}</td>
            <td class="align-middle small" data-payment="comment">${(payment.noContrib ? 'PAF à zéro (' + payment.noContribReasonToString + ') ' : '')}
                ${this.sliceComment((payment.comment ?? '') + " \n" + (payment.commentExport ?? ''))}</td>
            <td class="align-middle" data-payment="createdAt">${this.formatDatetime(new Date(), 'date')}</td>
            <td class="align-middle text-center" data-payment="pdfGenerate">
                <span><i class="fas fa-file-pdf text-secondary fa-lg"></i></span>
            </td>
            <td class="align-middle text-center" data-payment="mailSent">
                <span><i class="fas fa-envelope text-secondary fa-lg"></i></span>
            </td>
            <td class="align-middle text-center">
                <button data-url="/payment/${payment.id}/delete" data-action="delete"
                    class="btn btn-danger btn-sm shadow my-1" data-placement="bottom" 
                        title="Supprimer l\'enregistrement" data-toggle="modal" data-target="#modal-block">
                    <span class="fas fa-trash-alt"></span>
                </button>
            </td>`
    }

    /**
     * Arrondi un nombre en valeur monétaire.
     * @param {Number} amount 
     */
    roundAmount(amount) {
        return amount ? Math.round(amount * 100) / 100 : ''
    }

    /**
     * Coupe un texte en un nombre maximum de caractères.
     * @param {String} comment 
     * @param {Number} limit 
     */
    sliceComment(comment, limit = 65) {
        if (' ' === comment) {
            return ''
        }

        if ( comment.length > limit) {
            return comment.slice(0, limit) + '...'
        }

        return comment
    }

    /**
     * Met à jour le nombre d'enregistrements.
     * @param {Number} value 
     */
    updateCounts(value) {
        this.countPaymentsElt.textContent = parseInt(this.countPaymentsElt.textContent) + value
        if (this.nbTotalPaymentsElt) {
            this.nbTotalPaymentsElt.textContent = parseInt(this.nbTotalPaymentsElt.textContent) + value
        }
    }

    /**
     * Donne la somme des montants.
     * @param {HTMLElement} elts 
     * @return {Number|String}
     */
    getSumAmts(elts) {
        let sumAmts = 0
        elts.forEach(elt => {
            let amount = elt.textContent
            if (amount) {
                sumAmts += parseFloat(amount.replace(' ', '').replace(',', '.'))
            }
        })
        if (!isNaN(sumAmts)) {
            return sumAmts
        }
        return 'Err.'
    }

    /**
     * Formate un nombre en valeur monétaire.
     * @param {Number} number 
     * @param {String} locale 
     */
    formatMoney(number, locale = 'fr') {
        return number || number === 0 ? number.toFixed(2).replace('.', ',') + ' €' : ''
    }

    /**
     * Formate une valeur texte en date.
     * @param {String} date 
     * @param {String} type 
     * @param {String} locale 
     */
    formatDatetime(date, type = 'datetime', locale = 'fr') {
        if (date === null) {
            return ''
        }

        date = new Date(date)

        switch (type) {
            case 'date':
                return date.toLocaleDateString(locale)
            case 'd/m':
                return date.toLocaleDateString(locale).substring(3, 10)
            case 'time':
                return date.toLocaleTimeString(locale).substring(0, 5)
            default:
                return date.toLocaleDateString(locale) + ' ' + date.toLocaleTimeString(locale).substring(0, 5)
        }
    }

    /**
     * Change la couleur du texte d'un élément on fonction de la valeur d'un nombre.
     * @param {HTMLElement} elt 
     * @param {String} value 
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

    /**
     * @param {Object} payment
     */
    deletedPaymentTr(payment) {
        document.getElementById('payment-'+payment.id).remove()
        this.updateCounts(-1)
    }

    /**
     * Redirects if there are no more lines.
     * @param {number} delay
     */
    checkToRedirect(delay) {
        if (document.querySelectorAll('table#table-payments tbody tr').length === 0) {
            setTimeout(() => {
                document.location.href = location.pathname
            }, delay * 1000)    
        }
    }
}