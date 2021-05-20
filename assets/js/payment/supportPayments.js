import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import FormValidator from '../utils/form/formValidator'
import ParametersUrl from '../utils/parametersUrl'
import {Modal} from 'bootstrap'
import FieldDisplayer from '../utils/form/fieldDisplayer'
import ContributionCalcul from './contributionCalcul'

export default class SupportPayments {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.formValidator = new FormValidator()
        this.parametersUrl = new ParametersUrl()

        this.paymentModalElt = new Modal(document.getElementById('payment-modal'))

        this.btnNewElt = document.querySelector('button[data-action="new_payment"')
        this.trElt = null

        this.modalConfirmElt = document.getElementById('modal-confirm')

        this.sumToPayAmtElt = document.querySelector('td[data-payment="sumToPayAmt"]')
        this.sumPaidAmtElt = document.querySelector('td[data-payment="sumPaidAmt"]')
        this.sumStillToPayAmtElt = document.querySelector('td[data-payment="sumStillToPayAmt"]')

        this.themeColor = document.getElementById('header').dataset.color
        this.countPaymentsElt = document.getElementById('count-payments')
        this.nbTotalPaymentsElt = document.getElementById('nb-total-payments')
        this.paymentId = null

        // Formulaire modal
        this.modalPaymentElt = document.getElementById('payment-modal')
        this.formPaymentElt = this.modalPaymentElt.querySelector('form[name=payment]')
        this.typeSelectElt = document.getElementById('payment_type')
        this.startDateInputElt = document.getElementById('payment_startDate')
        this.endDateInputElt = document.getElementById('payment_endDate')
        this.resourcesAmtInputElt = document.getElementById('payment_resourcesAmt')
        this.chargesAmtInputElt = document.getElementById('payment_chargesAmt')
        this.rentAmtInputElt = document.getElementById('payment_rentAmt')
        this.aplAmtInputElt = document.getElementById('payment_aplAmt')
        this.toPayAmtInputElt = document.getElementById('payment_toPayAmt')
        this.noContribInputElt = document.getElementById('payment_noContrib')
        this.noContribReasonSelectElt = document.getElementById('payment_noContribReason')
        this.paymentDateInputElt = document.getElementById('payment_paymentDate')
        this.paymentTypeSelectElt = document.getElementById('payment_paymentType')
        this.paidAmtInputElt = document.getElementById('payment_paidAmt')
        this.stillToPayAmtInputElt = document.getElementById('payment_stillToPayAmt')
        this.returnAmtInputElt = document.getElementById('payment_returnAmt')
        this.commentInputElt = document.getElementById('payment_comment')
        this.commentExportInputElt = document.getElementById('payment_commentExport')
        this.infoPaymentDivElt = document.getElementById('js-info-payment')

        this.pdfBtnElt = this.formPaymentElt.querySelector('button[data-action="create_pdf"]')
        this.mailBtnElt = this.formPaymentElt.querySelector('button[data-action="send_email"]')
        this.deleteBtnElt = this.formPaymentElt.querySelector('button[data-action="delete"]')
        this.saveBtnElt = this.formPaymentElt.querySelector('button[data-action="save"]')
        this.editBtnElts = this.formPaymentElt.querySelectorAll('button[data-edit]')

        this.contributionCalcul = new ContributionCalcul(this.formPaymentElt, this.afterCalculContribution.bind(this))

        this.now = new Date()
        this.error = false

        this.init()
    }

    init() {
        document.querySelectorAll('div[data-parent-field]').forEach(elt => {
            new FieldDisplayer(elt)
        })

        this.btnNewElt.addEventListener('click', () => {
            if (this.loader.isActive() === false) {
                this.newPayment()
            }
        })

        document.querySelectorAll('tr.payment').forEach(trElt => {
            const getBtnElt = trElt.querySelector('button[data-action="get"]')
            getBtnElt.addEventListener('click', () => {
                if (this.loader.isActive() === false) {
                    this.trElt = trElt
                    this.getPayment(parseInt(getBtnElt.dataset.id))
                }
            })
            const btnDeleteElt = trElt.querySelector('button[data-action="delete"]')
            btnDeleteElt.addEventListener('click', () => {
                this.trElt = trElt
                this.modalConfirmElt.dataset.url = btnDeleteElt.dataset.url
            })

        })

        this.resourcesAmtInputElt.addEventListener('input', () => this.checkResources())

        this.saveBtnElt.addEventListener('click', e => {
            e.preventDefault()
            if (this.loader.isActive() === false) {
                this.tryToSave()
            }
        })

        this.deleteBtnElt.addEventListener('click', e => {
            e.preventDefault()
            if (this.loader.isActive() === false) {
                this.deletePayment(this.deleteBtnElt.href)
            }
        })

        this.modalConfirmElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.modalConfirmElt.dataset.url, this.responseAjax.bind(this))
        })

        this.typeSelectElt.addEventListener('input', () => {
            this.initForm()
            this.checkType()
        })

        this.startDateInputElt.addEventListener('focusout', () => this.checkStartDate())
        this.endDateInputElt.addEventListener('focusout', () => this.checkEndDate())

        this.modalPaymentElt.querySelectorAll('input[data-amount]').forEach(elt => {
            elt.addEventListener('input', () => {
                this.checkMoney(elt)
            })
        })

        if (this.rentAmtInputElt) {
            [this.rentAmtInputElt, this.aplAmtInputElt].forEach(elt => {
                // elt.addEventListener('input', () => {
                //     // this.calculateAmountToPay()
                // })
            })
        }
            
        [this.toPayAmtInputElt, this.paidAmtInputElt].forEach(elt => {
            elt.addEventListener('input', () => {
                if (this.typeSelectElt.value) {
                    
                }
                this.calculateAmountStillDue()
            })
        })

        this.paymentDateInputElt.addEventListener('focusout', () => {
            this.checkPaidAmt()
        })

        this.calculateSumAmts()

        const paymentId = Number(this.parametersUrl.get('paymentId'))
        this.trElt = document.getElementById('payment-' + paymentId)
        if (this.trElt) {
            this.getPayment(paymentId)
        }


        if (this.noContribInputElt) {
            this.noContribInputElt.addEventListener('click', () => {
                if (this.noContribInputElt.checked === true) {
                    this.toPayAmtInputElt.value = 0
                }
            })
        }

        this.pdfBtnElt.addEventListener('click', e => {
            e.preventDefault()
            window.open(this.pdfBtnElt.dataset.url.replace('__id__', this.paymentId))         
        })
            
        this.mailBtnElt.addEventListener('click', e => {
            e.preventDefault()
            if (window.confirm('Confirmer l\'envoi du reçu par email au suivi ?')) {
                const url = this.mailBtnElt.dataset.url.replace('__id__', this.paymentId)
                this.ajax.send('GET', url, this.responseAjax.bind(this))
            }
        })
    }

    /**
     * Vérifie le type (redevance ou caution).
     */
    checkType() {
        const option = parseInt(this.typeSelectElt.value)

        // Masque tous les champs du formulaire.
        this.formPaymentElt.querySelectorAll('[data-payment]').forEach(elt => {
            elt.classList.add('d-none')
        })
        if ([1, 2, 10].includes(option)) { // Redevance, loyer, caution
            this.formPaymentElt.querySelector('label[for="payment_toPayAmt"]').textContent = 'Montant à régler'
        }
        if (option === 20) { // Prêt
            this.formPaymentElt.querySelector('label[for="payment_toPayAmt"]').textContent = 'Montant prêté'
        }
    }

    checkResources() {
        const noContribDivElt = this.formPaymentElt.querySelector('div[data-type="no_contrib"]')

        if (!noContribDivElt) {
           return null 
        }

        if (this.resourcesAmtInputElt.value > 0) {
            return noContribDivElt.classList.remove('d-none')
        }
        noContribDivElt.classList.add('d-none')
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
     * Calcule le restant dû.
     */
    calculateAmountStillDue() {
        if (!isNaN(this.toPayAmtInputElt.value) && !isNaN(this.paidAmtInputElt.value)) {
            this.stillToPayAmtInputElt.value = Math.round((this.toPayAmtInputElt.value - this.paidAmtInputElt.value) * 100) / 100
        }
        this.changeTextColor(this.stillToPayAmtInputElt, this.stillToPayAmtInputElt.value)
    }

    /**
     * Retourne vrai si le formualaire est valide.
     */
    isValidForm() {
        this.error = false
        const option = parseInt(this.typeSelectElt.value)

        this.checkPaymentType(option)
        this.checkContributionDate(option)
        this.checkToPaidAmt(option)
        this.checkReturnAmt(option)
        this.checkPaymentDate(option)
        this.checkPaymentMeans(option)
        this.checkPaidAmt(option)
        this.checkNoContribReason(option)

        return this.error != true
    }

    /**
     * Vérifie le type de paiement.
     * @param {Number} option 
     */
    checkPaymentType(option) {
        if (!option) {
            this.error = true
            return this.formValidator.invalidField(this.typeSelectElt, 'Saisie obligatoire.')
        }

        this.formValidator.validField(this.typeSelectElt)
    }

    /**
     * Vérifie la date de paiement.
     * @param {Number} option 
     */
    checkContributionDate(option) {
        if (![1, 2].includes(option)) { // PF et Loyer
            this.startDateInputElt.value = ''
            this.endDateInputElt.value = ''
            return null
        }

        this.checkStartDate()
        this.checkEndDate()
    }

    checkStartDate() {
        if (!this.startDateInputElt.value) {
            this.error = true
            return this.formValidator.invalidField(this.startDateInputElt, 'Saisie obligatoire.')
        } 
        
        this.formValidator.validField(this.startDateInputElt)
    }

    checkEndDate() {
        if (!this.endDateInputElt.value) {
            this.error = true
            return this.formValidator.invalidField(this.endDateInputElt, 'Saisie obligatoire.')
        } else if (this.endDateInputElt.value && new Date(this.endDateInputElt.value) <= new Date(this.startDateInputElt.value)) {
            this.error = true
            return this.formValidator.invalidField(this.endDateInputElt, 'La date doit être supérieure à la date de début.')
        }
        
        this.formValidator.validField(this.endDateInputElt)
    }

    /**
     * Vérfifie le montant à payer si redevance ou caution.
     * @param {Number} option 
     */
    checkToPaidAmt(option) {
        if (isNaN(this.toPayAmtInputElt.value)) {
            this.error = true
            return this.formValidator.invalidField(this.toPayAmtInputElt, 'Valeur invalide.')
        }
        if ([1, 2, 10, 20].includes(option) && !this.toPayAmtInputElt.value) { // PF, loyer, cautionn prêt
            this.error = true
            return this.formValidator.invalidField(this.toPayAmtInputElt, 'Saisie obligatoire.')
        }
        return this.formValidator.validField(this.toPayAmtInputElt)
    }

    /**
     * Vérifie le montant restitué si Restitution caution.
     * @param {Number} option 
     */
    checkReturnAmt(option) {
        if (isNaN(this.returnAmtInputElt.value)) {
            this.error = true
            return this.formValidator.invalidField(this.returnAmtInputElt, 'Valeur invalide.')
        }
        if (option == 11 && !this.returnAmtInputElt.value) { // Restitution Caution
            this.error = true
            return this.formValidator.invalidField(this.returnAmtInputElt, 'Saisie obligatoire.')
        }
    }

    /**
     * Vérifie le montant du paiement saisi.
     * @param {Number} option 
     */
    checkPaidAmt(option) {
        if (isNaN(this.paidAmtInputElt.value)) {
            this.error = true
            return this.formValidator.invalidField(this.paidAmtInputElt, 'Valeur invalide.')
        }
        if ((!this.paidAmtInputElt.value && [1, 2, 10].includes(option)
            && (this.paymentDateInputElt.value || this.paymentTypeSelectElt.value))
            || (!this.paidAmtInputElt.value && 30 === option)) {
            this.error = true
            return this.formValidator.invalidField(this.paidAmtInputElt, 'Saisie obligatoire.')
        }
        return this.formValidator.validField(this.paidAmtInputElt)
    }

    /**
     * Vérifie la date du paiement.
     * @param {Number} option 
     */
    checkPaymentDate(option) {
        const intervalWithNow = (this.now - new Date(this.paymentDateInputElt.value)) / (1000 * 60 * 60 * 24)

        if ((this.paymentDateInputElt.value && !intervalWithNow) || intervalWithNow > (365 * 19)) {
            this.error = true
            return this.formValidator.invalidField(this.paymentDateInputElt, 'Date invalide.')
        }
        if (intervalWithNow < 0) {
            this.error = true
            return this.formValidator.invalidField(this.paymentDateInputElt, 'La date ne peut être postérieure à la date du jour.')
        }
        if (!this.paymentDateInputElt.value && (option === 20 || this.paidAmtInputElt.value > 0
            || this.paymentTypeSelectElt.value) || this.returnAmtInputElt.value > 0) {
            this.error = true
            return this.formValidator.invalidField(this.paymentDateInputElt, 'Saisie obligatoire.')
        }
        return this.formValidator.validField(this.paymentDateInputElt)
    }

    /**
     * Vérifie le moyen de paiement saisi.
     * @param {Number} option 
     */
    checkPaymentMeans(option) {
        if (!this.paymentTypeSelectElt.value && (option === 20 || this.paymentDateInputElt.value
            || this.paidAmtInputElt.value > 0 || this.returnAmtInputElt.value > 0)) {
            this.error = true
            return this.formValidator.invalidField(this.paymentTypeSelectElt, 'Saisie obligatoire.')
        }
        return this.formValidator.validField(this.paymentTypeSelectElt)
    }

    /**
     * @param {Number} option 
     */
    checkNoContribReason(option) {
        if (1 != option || null === this.noContribInputElt) {
            return null
        }
         
        if (this.noContribInputElt.checked === true && !this.noContribReasonSelectElt.value) {
            this.error = true
            return this.formValidator.invalidField(this.noContribReasonSelectElt, 'Saisie obligatoire.')
        }
        return this.formValidator.validField(this.noContribReasonSelectElt)
    }

    /**
     * Affiche un formulaire modal vierge.
     */
    newPayment() {
        this.paymentId = null
        this.paymentModalElt.show()
        this.typeSelectElt.value = ''
        this.initForm()
        this.checkType()
        this.deleteBtnElt.classList.replace('d-block', 'd-none')
        this.saveBtnElt.textContent = 'Enregistrer'
        this.modalPaymentElt.querySelector('form').action = this.btnNewElt.dataset.url
        document.getElementById('show-calcul-contribution-btn').classList.add('d-none')
        this.editBtnElts.forEach(elt => {
            elt.classList.add('d-none')
        })
    }

    /**
     * Requête pour obtenir le RDV sélectionné dans le formulaire modal.
     * @param {String} id 
     */
    getPayment(id) {
        this.loader.on()

        this.paymentId = id
        this.formPaymentElt.action = this.formPaymentElt.dataset.url.replace('__id__', id)

        this.deleteBtnElt.classList.replace('d-none', 'd-block')
        this.deleteBtnElt.href = this.deleteBtnElt.dataset.url.replace('__id__', id)
        this.saveBtnElt.textContent = 'Mettre à jour'

        this.initForm()
        this.checkType()

        this.ajax.send('GET', '/payment/' + id + '/get', this.responseAjax.bind(this))
    }

    /**
     * Réinitialise le formulaire.
     */
    initForm() {
        this.formValidator.reinit()
        this.formPaymentElt.querySelectorAll('input, textarea').forEach(inputElt => {
            if (inputElt.type != 'hidden') {
                inputElt.value = null
            }
            if (inputElt.type === 'checkbox') {
                inputElt.checked = false      
            }
        })
        this.paymentTypeSelectElt.value = ''
        if (this.noContribReasonSelectElt) {
            this.noContribReasonSelectElt.value = ''
        }
        this.infoPaymentDivElt.innerHTML = ''
        this.checkResources()
        this.editBtnElts.forEach(elt => {
            elt.classList.add('d-none')
        })
    }

    /**
     * Enregistre l'opération.
     */
    tryToSave() {
        this.loader.on()
        if (this.isValidForm()) {
            const url = this.formPaymentElt.getAttribute('action')
            this.ajax.send('POST', url, this.responseAjax.bind(this), new FormData(this.formPaymentElt))
        } else {
            new MessageFlash('danger', 'Veuillez corriger le(s) erreur(s) avant d\'enregistrer.')
            this.loader.off()
        }
    }

    /**
     * Envoie une requête ajax pour supprimer l 'enregistrement.
     * @param {String} url 
     */
    deletePayment(url) {
        if (window.confirm('Confirmer la suppression cet enregistrement ?')) {
            this.loader.on()
            this.ajax.send('GET', url, this.responseAjax.bind(this))
        }
    }

    /**
     * Réponse du serveur.
     * @param {Object} response 
     */
    responseAjax(response) {
        switch (response.action) {
            case 'show':
                this.showPayment(response.data)
                break
            case 'create':
                this.createPayment(response.data.payment)
                new MessageFlash(response.alert, response.msg)
                break
            case 'update':
                this.updatePayment(response.data.payment)
                new MessageFlash(response.alert, response.msg)
                break
            case 'delete':
                this.trElt.remove()
                this.updateCounts(-1)
                this.loader.off()
                this.paymentModalElt.hide()
                new MessageFlash(response.alert, response.msg)
                break
            default:
                this.loader.off()
                new MessageFlash(response.alert, response.msg)
                break
        }
        this.loading = false
        this.calculateSumAmts()
    }

    /**
     * @param {Object} payment 
     */
    afterCalculContribution(payment) {
        this.startDateInputElt.value = payment.startDate.substring(0, 10)
        this.endDateInputElt.value = payment.endDate.substring(0, 10)
        this.resourcesAmtInputElt.value = payment.resourcesAmt
        this.chargesAmtInputElt.value = payment.chargesAmt
        this.rentAmtInputElt.value = payment.rentAmt
        this.toPayAmtInputElt.value = payment.toPayAmt

        this.formPaymentElt.querySelector('#payment_contributionRate').value = payment.contributionRate
        this.formPaymentElt.querySelector('#payment_nbConsumUnits').value = payment.nbConsumUnits

        this.checkResources()
    }

    /**
     * Donne la redevance sélectionnée dans le formulaire modal.
     * @param {Object} data 
     */
    showPayment(data) {
        const payment = data.payment
        this.paymentModalElt.show()
        this.typeSelectElt.value = payment.type
        this.startDateInputElt.value = payment.startDate ? payment.startDate.substring(0, 10) : null
        this.endDateInputElt.value = payment.endDate ? payment.endDate.substring(0, 10) : null
        this.resourcesAmtInputElt.value = payment.resourcesAmt
        this.chargesAmtInputElt.value = payment.chargesAmt
        this.rentAmtInputElt.value = payment.rentAmt
        this.toPayAmtInputElt.value = payment.toPayAmt
        this.paymentDateInputElt.value = payment.paymentDate ? payment.paymentDate.substring(0, 10) : null
        this.paymentTypeSelectElt.value = payment.paymentType
        this.paidAmtInputElt.value = payment.paidAmt
        this.stillToPayAmtInputElt.value = Math.round(payment.stillToPayAmt * 100) / 100
        this.returnAmtInputElt.value = payment.returnAmt
        this.commentInputElt.value = payment.comment
        this.commentExportInputElt.value = payment.commentExport;
        
        this.formPaymentElt.querySelector('#payment_contributionRate').value = payment.contributionRate
        this.formPaymentElt.querySelector('#payment_nbConsumUnits').value = payment.nbConsumUnits

        this.infoPaymentDivElt.innerHTML = this.getInfoPaymentElt(data)
        this.checkType()
        this.typeSelectElt.click()
        if (payment.id) {
            this.editBtnElts.forEach(elt => {
                elt.classList.remove('d-none')
            })
        }
        this.loader.off()
    }

    /**  
     * Donnes les informations sur l'enregistrement (date de création, créateur...).
     * @param {Object} data
     */
    getInfoPaymentElt(data) {
        const payment = data.payment

        let htmlContent = `Créé le ${this.formatDatetime(payment.createdAt)} par ${data.createdBy}`
        if (payment.createdAt != payment.updatedAt) {
            htmlContent = htmlContent + `<br/> (modifié le ${this.formatDatetime(payment.updatedAt)} par ${data.updatedBy})`
        }
        return htmlContent
    }

    /**
     * Crée la ligne de la nouvelle redevance dans le tableau.
     * @param {Array} payment 
     */
    createPayment(payment) {

        this.formPaymentElt.action = this.formPaymentElt.dataset.url.replace('__id__', payment.id)
        this.editBtnElts.forEach(elt => {
            elt.classList.remove('d-none')
        })

        this.saveBtnElt.textContent = 'Mettre à jour'

        const paymentElt = document.createElement('tr')
        paymentElt.className = 'js-payment'
        paymentElt.innerHTML = this.getPrototypePayment(payment)
        this.trElt = paymentElt

        const containerPaymentsElt = document.getElementById('container-payments')
        containerPaymentsElt.insertBefore(paymentElt, containerPaymentsElt.firstChild)
        this.updateCounts(1)

        this.calculateSumAmts()

        const getBtnElt = paymentElt.querySelector('button[data-action="get"]')
        getBtnElt.addEventListener('click', () => {
            if (this.loader.isActive() === false) {
                this.trElt = paymentElt
                this.getPayment(parseInt(getBtnElt.dataset.id))
            }
        })

        const btnDeleteElt = paymentElt.querySelector('button[data-action="delete"]')
        btnDeleteElt.addEventListener('click', () => {
            this.trElt = paymentElt
            this.modalConfirmElt.dataset.url = btnDeleteElt.dataset.url
        })
        this.loader.off()
    }

    /**
     * Met à jour la ligne du tableau correspondant au paiement.
     * @param {Object} payment 
     */
    updatePayment(payment) {
        this.trElt.querySelector('td[data-payment="type"]').textContent = payment.typeToString + (payment.type == 11 ? ' (' + this.formatMoney(payment.returnAmt) + ')' : '')
        this.trElt.querySelector('td[data-payment="startDate"]').textContent = this.formatDatetime(payment.startDate, 'date') + ' - ' + this.formatDatetime(payment.endDate, 'date')
        this.trElt.querySelector('td[data-payment="toPayAmt"]').textContent = this.formatMoney(payment.toPayAmt)
        this.trElt.querySelector('td[data-payment="paidAmt"]').textContent = this.formatMoney(payment.paidAmt)
        this.trElt.querySelector('td[data-payment="stillToPayAmt"]').textContent = this.formatMoney(this.roundAmount(payment.stillToPayAmt))
        this.trElt.querySelector('td[data-payment="paymentDate"]').textContent = this.formatDatetime(payment.paymentDate, 'date')
        this.trElt.querySelector('td[data-payment="paymentType"]').textContent = payment.paymentTypeToString
        this.trElt.querySelector('td[data-payment="comment"]').textContent = this.sliceComment((payment.comment ?? '')  + " \n" + (payment.commentExport ?? ''))
        this.calculateSumAmts()
        this.loader.off()
    }

    /**
     * Crée la ligne du paiement.
     * @param {Object} payment 
     */
    getPrototypePayment(payment) {
        return `
            <td scope="row" class="align-middle text-center">
                <button class="btn btn-${this.themeColor} btn-sm shadow" data-action="get" data-id="${payment.id}" 
                    data-url="/payment/${payment.id}/get" data-toggle="tooltip" 
                    data-placement="bottom" title="Voir l'enregistrement"><span class="fas fa-eye"></span>
                </button>
            </td>
            <td class="align-middle" data-payment="type">${payment.typeToString}<br/>
                <span class="text-secondary">${payment.type == 11 ? ' (' + this.formatMoney(payment.returnAmt) + ')' : '' }</span>
            </td>
            <td class="align-middle" data-payment="startDate">${this.formatDatetime(payment.startDate, 'date') + ' - ' + this.formatDatetime(payment.endDate, 'date')}</td>
            <td class="align-middle text-right" data-payment="toPayAmt">${this.formatMoney(payment.toPayAmt)}</td>
            <td class="align-middle text-right" data-payment="paidAmt">${this.formatMoney(payment.paidAmt)}</td>
            <td class="align-middle text-right" data-payment="stillToPayAmt">${this.formatMoney(this.roundAmount(payment.stillToPayAmt))}</td>
            <td class="align-middle text-center" data-payment="paymentDate">${this.formatDatetime(payment.paymentDate, 'date')}</td>
            <td class="align-middle" data-payment="paymentType">${payment.paymentType ? payment.paymentTypeToString : ''}</td>
            <td class="align-middle" data-payment="comment">${this.sliceComment((payment.comment ?? '')  + " \n" + (payment.commentExport ?? ''))}</td>
            <td class="align-middle" data-payment="createdAt">${this.formatDatetime(this.now, 'date')}</td>
            <td class="align-middle text-center" data-payment="pdfGenerate">
                <span><i class="fas fa-file-pdf text-secondary fa-lg"></i></span>
            </td>
            <td class="align-middle text-center" data-payment="mailSent">
                <span><i class="fas fa-envelope text-secondary fa-lg"></i></span>
            </td>
            <td class="align-middle text-center">
                <button data-url="/payment/${payment.id}/delete" data-action="delete"
                    class="btn btn-danger btn-sm shadow my-1" data-placement="bottom" 
                        title="Supprimer l"enregistrement" data-toggle="modal" data-target="#modal-block">
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
     * Vérifie si le montant saisie est valide.
     * @param {inputElt} moneyElt 
     */
    checkMoney(moneyElt) {
        let value = moneyElt.value
        value = value.replace(' ', '').replace(',', '.')
        if (Number(value) >= 0) {
            return this.formValidator.validField(moneyElt)
        }
        return this.formValidator.invalidField(moneyElt, 'Valeur invalide.')
    }

    /**
     * Vérifie si la date est valide.
     * @param {inputElt} dateElt 
     */
    checkDate(dateElt) {
        const interval = Math.round((this.now - new Date(dateElt.value)) / (1000 * 60 * 60 * 24))
        if ((dateElt.value && !Number.isInteger(interval)) || interval > (365 * 99) || interval < -(365 * 99)) {
            return this.formValidator.invalidField(dateElt, 'Date invalide.')
        }
        return this.formValidator.validField(dateElt)
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
                break
            case 'd/m':
                return date.toLocaleDateString(locale).substring(3, 10)
                break
            case 'time':
                return date.toLocaleTimeString(locale).substring(0, 5)
                break
            default:
                return date.toLocaleDateString(locale) + ' ' + date.toLocaleTimeString(locale).substring(0, 5)
                break
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
}