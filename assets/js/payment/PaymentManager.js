import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import {Modal} from 'bootstrap'
import PaymentForm from "./PaymentForm";

export default class PaymentManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.paymentForm = new PaymentForm(this)

        this.paymentModal = new Modal(document.getElementById('payment_modal'))

        this.btnNewElt = document.querySelector('button[data-action="new_payment"]')

        this.confirmBtnElt = document.getElementById('modal-confirm')
        document.querySelector('#modal-block div.modal-content').classList.add('bg-light')

        this.sumToPayAmtElt = document.querySelector('td[data-payment="sumToPayAmt"]')
        this.sumPaidAmtElt = document.querySelector('td[data-payment="sumPaidAmt"]')
        this.sumStillToPayAmtElt = document.querySelector('td[data-payment="sumStillToPayAmt"]')

        this.countPaymentsElt = document.getElementById('count_payments')
        this.nbTotalPaymentsElt = document.getElementById('nb_total_payments')

        this.now = new Date()
        this.init()
    }

    init() {
        this.btnNewElt.addEventListener('click', () => {
            if (this.loader.isActive() === false) {
                this.newPayment()
            }
        })

        document.querySelectorAll('table#table_payments tbody tr button[data-action="show"]')
            .forEach(showBtnElt => showBtnElt
                .addEventListener('click', () => this.requestShowPayment(showBtnElt)))

        document.querySelectorAll('table#table_payments tbody tr td a[data-action="generate-pdf"]')
            .forEach(aElt => aElt.addEventListener('click', () => {
                setTimeout(() => {
                    aElt.querySelector('i').classList.replace('text-secondary', 'text-success')
                }, 1000)
            }))
        document.querySelectorAll('table#table_payments tbody tr td a[data-action="send-mail"]')
            .forEach(aElt => aElt.addEventListener('click', e => {
                e.preventDefault()
                this.requestSendMail(aElt.href)
            }))

        document.querySelectorAll('table#table_payments tbody tr button[data-action="delete"]')
            .forEach(btnDeleteElt => btnDeleteElt
                .addEventListener('click', () => this.confirmBtnElt.dataset.path = btnDeleteElt.dataset.path))

        document.querySelectorAll('button[data-action="restore"]').forEach(restoreBtn => restoreBtn
            .addEventListener('click', () => this.requestRestorePayment(restoreBtn)))

        this.confirmBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.confirmBtnElt.dataset.path, this.responseAjax.bind(this))
        })

        this.calculateSumAmts()
    }

    /**
     * @param {HTMLLinkElement} restoreBtn
     */
    requestRestorePayment(restoreBtn) {
        if (!this.loader.isActive()) {
            this.loader.on()

            this.ajax.send('GET', restoreBtn.dataset.path, this.responseAjax.bind(this))
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
        this.paymentModal.show()
    }

    /**
     * Permet de voir un paiement
     * @param {HTMLButtonElement} btnShowElt
     */
    requestShowPayment(btnShowElt) {
        if (!this.loader.isActive()) {
            this.paymentForm.formPaymentElt.action = this.paymentForm.formPaymentElt.dataset.path
                .replace('__id__', btnShowElt.dataset.id)

            this.loader.on()

            this.ajax.send('GET', btnShowElt.dataset.path, this.responseAjax.bind(this))
        }
    }

    /**
     * @param {string} path
     */
    requestSendMail(path) {
        if (window.confirm('Confirmer l\'envoi du reçu par email au suivi ?')) {
            this.ajax.send('GET', path, this.responseAjax.bind(this))
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
            case 'send_receipt':
                this.updatePaymentPictoMail(payment)
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
            this.paymentModal.hide()

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
        this.paymentModal.show()
    }

    /**
     * Crée la ligne de la nouvelle redevance dans le tableau.
     * @param {Array} payment 
     */
    createPayment(payment) {
        this.paymentForm.formPaymentElt.action = this.paymentForm.formPaymentElt.dataset.path.replace('__id__', payment.id)
        this.paymentForm.editBtnElts.forEach(elt => elt.classList.remove('d-none'))

        this.paymentForm.saveBtnElt.querySelector('span').textContent = 'Mettre à jour'

        const paymentElt = document.createElement('tr')
        paymentElt.id = 'payment-'+payment.id
        paymentElt.className = 'js-payment'
        paymentElt.innerHTML = this.getPrototypePayment(payment)

        const containerPaymentsElt = document.getElementById('container_payments')
        containerPaymentsElt.insertBefore(paymentElt, containerPaymentsElt.firstChild)
        this.updateCounts(1)

        this.calculateSumAmts()

        const btnShowElt = paymentElt.querySelector('button[data-action="show"]')
        btnShowElt.addEventListener('click', () => this.requestShowPayment(btnShowElt))

        const btnSendMail = paymentElt.querySelector('a[data-action="send-mail"]')
        btnSendMail.addEventListener('click', e => {
            e.preventDefault()
            this.requestSendMail(btnSendMail.href)
        })

        const btnDeleteElt = paymentElt.querySelector('button[data-action="delete"]')
        btnDeleteElt.addEventListener('click', () => this.confirmBtnElt.dataset.path = btnDeleteElt.dataset.path)
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
        trElt.querySelector('td[data-payment="paidAmt"]').textContent = payment.paidAmt ? this.formatMoney(payment.paidAmt) : ''
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
        const pathSendMail = document.querySelector('table#table_payments')
            .dataset.pathSendMail.replace('__id__', payment.id)

        return `
            <td class="align-middle text-center">
                <button class="btn btn-primary btn-sm shadow" data-action="show" data-id="${payment.id}" 
                    data-path="/payment/${payment.id}/show" data-bs-toggle="tooltip" 
                    data-bs-placement="bottom" title="Voir l'enregistrement"><span class="fas fa-eye"></span>
                </button>
            </td>
            <td class="align-middle" data-payment="type">${payment.typeToString}<br/>
                <span class="text-secondary">${payment.type === 11 ? ' (' + this.formatMoney(payment.returnAmt) + ')' : '' }</span>
            </td>
            <td class="align-middle text-center" data-payment="startDate">${this.formatDatetime(payment.startDate, 'date') + ' - ' + this.formatDatetime(payment.endDate, 'date')}</td>
            <td class="align-middle text-end" data-payment="toPayAmt">${this.formatMoney(payment.toPayAmt)}</td>
            <td class="align-middle text-end" data-payment="paidAmt">${this.formatMoney(payment.paidAmt)}</td>
            <td class="align-middle text-end" data-payment="stillToPayAmt">${this.formatMoney(this.roundAmount(payment.stillToPayAmt))}</td>
            <td class="align-middle text-center" data-payment="paymentDate">${this.formatDatetime(payment.paymentDate, 'date')}</td>
            <td class="align-middle" data-payment="paymentType">${payment.paymentType ? payment.paymentTypeToString : ''}</td>
            <td class="align-middle small" data-payment="comment">${(payment.noContrib ? 'PAF à zéro (' + payment.noContribReasonToString + ') ' : '')}
                ${this.sliceComment((payment.comment ?? '') + " \n" + (payment.commentExport ?? ''))}</td>
            <td class="align-middle" data-payment="createdAt">${this.formatDatetime(new Date(), 'date')}</td>
            <td class="align-middle text-center" data-payment="pdfGenerate">
                <a href="/payment/${payment.id}/export/pdf"
                    <i class="fas fa-file-pdf text-secondary fa-lg"></i>
                </a>
            </td>
            <td class="align-middle text-center" data-payment="mailSent">
                <a href="${pathSendMail}" data-action="send-mail">
                    <i class="fas fa-envelope text-secondary fa-lg"></i>
                </a>
            </td>
            <td class="align-middle text-center">
                <button data-path="/payment/${payment.id}/delete" data-action="delete"
                    class="btn btn-danger btn-sm shadow my-1" data-bs-placement="bottom" 
                        title="Supprimer l\'enregistrement" data-bs-toggle="modal" data-bs-target="#modal-block">
                    <span class="fas fa-trash-alt"></span>
                </button>
            </td>
        `
    }

    /**
     * Arrondi un nombre en valeur monétaire.
     * @param {number} amount 
     */
    roundAmount(amount) {
        return amount ? Math.round(amount * 100) / 100 : ''
    }

    /**
     * Coupe un texte en un nombre maximum de caractères.
     * @param {string} comment 
     * @param {number} limit 
     */
    sliceComment(comment, limit = 65) {
        if (comment === '') {
            return ''
        }

        if ( comment.length > limit) {
            return comment.slice(0, limit) + '...'
        }

        return comment
    }

    /**
     * Met à jour le nombre d'enregistrements.
     * @param {number} value 
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
     * @return {number|string}
     */
    getSumAmts(elts) {
        let sumAmts = 0
        elts.forEach(elt => {
            let amount = parseFloat(elt.textContent.replaceAll(' ', '').replace(',', '.'))
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
     * Formate un nombre en valeur monétaire.
     * @param {number} number 
     * @param {string} locale 
     */
    formatMoney(number, locale = 'fr') {
        return number || number === 0 ? number.toFixed(2).replace('.', ',') + ' €' : ''
    }

    /**
     * Formate une valeur texte en date.
     * @param {string} date 
     * @param {string} type 
     * @param {string} locale 
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
        if (document.querySelectorAll('table#table_payments tbody tr').length === 0) {
            setTimeout(() => {
                document.location.href = location.pathname
            }, delay * 1000)    
        }
    }

    /**
     * @param {Object} payment
     */
    updatePaymentPictoMail(payment) {
        const trElt = document.getElementById('payment-'+payment.id)
        const pictoElt = trElt.querySelector('td a[data-action="send-mail"] i')

        if (pictoElt.classList.contains('fa-envelope')) {
            pictoElt.classList.replace('text-secondary', 'text-success')
        }
    }
}