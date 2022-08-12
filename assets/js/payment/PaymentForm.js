import PaymentManager from './PaymentManager'
import FormValidator from "../utils/form/formValidator"
import AlertMessage from "../utils/AlertMessage"
import FieldDisplayer from "../utils/form/fieldDisplayer"
import ContributionCalcul from "./ContributionCalcul"
import DateFormatter from '../utils/date/DateFormatter'

export default class PaymentForm {

    /**
     * @param {PaymentManager} paymentManager
     */
    constructor(paymentManager) {
        this.paymentManager = paymentManager
        this.loader = paymentManager.loader
        this.ajax = paymentManager.ajax
        this.responseAjax = paymentManager.responseAjax.bind(paymentManager)

        // Formulaire modal
        this.paymentModalElt = paymentManager.modalElt
        this.formPaymentElt = this.paymentModalElt.querySelector('form[name=payment]')
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
        this.infoPaymentElt = document.querySelector('[data-payment="info"]')

        this.btnExportPdfElt = this.formPaymentElt.querySelector('button[data-action="export_pdf"]')
        this.btnSendmailElt = this.formPaymentElt.querySelector('button[data-action="send_email"]')
        this.btnDeletelt = this.formPaymentElt.querySelector('button[data-action="delete"]')
        this.btnSavelt = this.formPaymentElt.querySelector('button[data-action="save"]')
        this.btnEditElts = this.formPaymentElt.querySelectorAll('button[data-edit]')

        this.btnConfirmElt = document.getElementById('modal_confirm_btn')

        this.formValidator = new FormValidator(this.formPaymentElt)
        this.contributionCalcul = new ContributionCalcul(this.formPaymentElt, this.afterCalculContribution.bind(this))
        this.dateFormatter = new DateFormatter()

        this.payment = null
        this.isValid = true
        this.displayedFields = []

        this.init()
    }

    init() {
        document.querySelectorAll('div[data-parent-field]')
            .forEach(elt => this.displayedFields.push(new FieldDisplayer(elt)))

        this.paymentModalElt.querySelectorAll('input[data-amount]')
            .forEach(elt => elt
                .addEventListener('input', () => this.formValidator.checkAmount(elt, 0, 9999)))

        this.typeSelectElt.addEventListener('input', () => {
            this.#resetForm()
            this.checkType()
        })

        this.paymentDateInputElt.addEventListener('focusout', () => this.checkPaymentDate())
        this.resourcesAmtInputElt.addEventListener('input', () => this.checkResources())

        this.startDateInputElt.addEventListener('focusout', () => this.checkStartDate())
        this.endDateInputElt.addEventListener('focusout', () => this.checkEndDate())

        this.toPayAmtInputElt.addEventListener('input', () => this.calculateAmountStillDue())
        this.paidAmtInputElt.addEventListener('input', () => this.calculateAmountStillDue())

        this.btnSavelt.addEventListener('click', e => {
            e.preventDefault()
            this.tryToSave()
        })

        this.btnDeletelt.addEventListener('click', e => {
            e.preventDefault()
            this.paymentManager.showModalConfirm(this.payment.id)
        })

        this.btnExportPdfElt.addEventListener('click', e => {
            e.preventDefault()
            this.paymentManager.requestExportPdf(this.payment.id)
        })
        
        this.btnSendmailElt.addEventListener('click', e => {
            e.preventDefault()
            this.paymentManager.requestSendEmail(this.payment.id)
        })

        if (this.rentAmtInputElt) {
            [this.rentAmtInputElt, this.aplAmtInputElt].forEach(elt => {
                elt.addEventListener('input', () => this.calculateAmountToPay())
            })
        }
        if (this.noContribInputElt) {
            this.noContribInputElt.addEventListener('click', () => {
                if (this.noContribInputElt.checked === true) {
                    this.toPayAmtInputElt.value = 0
                }
            })
        }
    }

    tryToSave() {
        if (this.loader.isActive() === false) {
            this.loader.on()

            if (this.isValidForm()) {
                this.ajax.send(
                    'POST',
                    this.formPaymentElt.action,
                    this.responseAjax,
                    new FormData(this.formPaymentElt)
                )
            } else {
                new AlertMessage('danger', 'Veuillez corriger le(s) erreur(s) avant d\'enregistrer.')
                this.loader.off()
            }
        }
    }

    /**
     * Show payment in form.
     * @param {Object} payment
     */
    show(payment) {
        this.#resetForm()

        this.payment = payment

        this.formPaymentElt.action = this.paymentManager.pathEdit(payment.id)

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
        this.commentExportInputElt.value = payment.commentExport

        const noContribCheckboxElt = this.formPaymentElt.querySelector('#payment_noContrib')
        if (noContribCheckboxElt) {
            noContribCheckboxElt.checked = payment.noContrib
            this.formPaymentElt.querySelector('#payment_noContribReason').value = payment.noContribReason ?? ''
        }

        this.btnDeletelt.classList.replace('d-none', 'd-block')
        this.btnSavelt.querySelector('span').textContent = 'Mettre à jour'

        this.formPaymentElt.querySelector('#payment_contributionRate').value = payment.contributionRate
        this.formPaymentElt.querySelector('#payment_nbConsumUnits').value = payment.nbConsumUnits

        this.infoPaymentElt.innerHTML = this.getInfoPaymentElt(payment)
        this.checkType()
        this.checkResources()

        if (payment.id) {
            this.btnEditElts.forEach(elt => {
                elt.classList.remove('d-none')
            })
        }
    }

    new() {
        this.typeSelectElt.value = ''
        this.#resetForm()

        this.formPaymentElt.action = this.paymentManager.pathCreate()

        this.checkType()
        this.btnDeletelt.classList.replace('d-block', 'd-none')
        this.btnSavelt.querySelector('span').textContent = 'Enregistrer'
        document.getElementById('show_calcul_contribution_btn').classList.add('d-none')
        this.btnEditElts.forEach(elt => elt.classList.add('d-none'))
    }

    /**
     * Donnes les informations sur l'enregistrement (date de création, créateur...).
     * @param {Object} payment
     */
    getInfoPaymentElt(payment) {
        let htmlContent = `Créé le ${this.dateFormatter.format(payment.createdAt)} par ${payment.createdBy.fullname}`
        if (payment.createdAt !== payment.updatedAt) {
            htmlContent = htmlContent + `<br/> (modifié le ${this.dateFormatter.format(payment.updatedAt)} par ${payment.updatedBy.fullname})`
        }
        return htmlContent
    }

    #resetForm() {
        this.formValidator.reinit()
        this.formPaymentElt.querySelectorAll('input:not([type="hidden"]), select:not([id="payment_type"]), textarea').forEach(fieldElt => {
            fieldElt.value = ''
            if (fieldElt.type === 'checkbox') {
                fieldElt.checked = false
            }
        })
        this.infoPaymentElt.innerHTML = ''
        this.checkResources()
        this.btnEditElts.forEach(elt => elt.classList.add('d-none'))
    }

    /**
     * Vérifie le type (redevance ou caution).
     */
    checkType() {
        this.paymentTypeValue = parseInt(this.typeSelectElt.value)

        // Masque tous les champs du formulaire.
        this.formPaymentElt.querySelectorAll('[data-payment]').forEach(elt => {
            elt.classList.add('d-none')
        })
        if ([1, 2, 10].includes(this.paymentTypeValue)) { // Redevance, loyer, caution
            this.formPaymentElt.querySelector('label[for="payment_toPayAmt"]').textContent = 'Montant à régler'
        }
        if (20 === this.paymentTypeValue) { // Prêt
            this.formPaymentElt.querySelector('label[for="payment_toPayAmt"]').textContent = 'Montant prêté'
        }

        this.displayedFields.forEach(displayedField => displayedField.check())
    }

    checkResources() {
        const noContribDivElt = this.formPaymentElt.querySelector('div[data-type="no_contrib"]')

        if (!noContribDivElt) {
            return null
        }

        if (this.resourcesAmtInputElt.value > 0) {
            noContribDivElt.classList.remove('d-none')
            setTimeout(() => {
                noContribDivElt.classList.add('fade-in')
                noContribDivElt.classList.remove('fade-out')
            }, 10)
        } else {
            noContribDivElt.classList.add('d-none', 'fade-out')
            noContribDivElt.classList.remove('fade-in')
        }
    }

    /**
     * Retourne vrai si le formulaire est valide.
     * @return {Boolean}
     */
    isValidForm() {

        this.formPaymentElt.classList.add('was-validated')

        this.isValid = true
        this.paymentTypeValue = parseInt(this.typeSelectElt.value)

        this.checkPaymentType()
        this.checkContributionDate()
        this.checkToPaidAmt()
        this.checkReturnAmt()
        this.checkPaymentDate()
        this.checkPaymentMeans()
        this.checkPaidAmt()
        this.checkNoContribReason()

        return this.isValid
    }

    calculateAmountToPay() {
        if (!isNaN(this.rentAmtInputElt.value) && !isNaN(this.aplAmtInputElt.value)) {
            this.toPayAmtInputElt.value = Math.round((this.rentAmtInputElt.value - this.aplAmtInputElt.value) * 100) / 100
        }
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

        this.checkStartDate()
        this.checkEndDate()
        this.checkToPaidAmt()
        this.checkResources()
    }

    /**
     * Calcule le restant dû.
     */
    calculateAmountStillDue() {
        if (!isNaN(this.toPayAmtInputElt.value) && !isNaN(this.paidAmtInputElt.value)) {
            this.stillToPayAmtInputElt.value = Math.round((this.toPayAmtInputElt.value - this.paidAmtInputElt.value) * 100) / 100
        }
        this.paymentManager.changeTextColor(this.stillToPayAmtInputElt, this.stillToPayAmtInputElt.value)
    }

    /**
     * Vérifie le type de paiement.
     */
    checkPaymentType() {
        if (!this.paymentTypeValue) {
            this.isValid = false
            return this.formValidator.invalidField(this.typeSelectElt, 'Saisie obligatoire.')
        }

        this.formValidator.validField(this.typeSelectElt)
    }
    /**
     * Vérifie la date de paiement.
     */
    checkContributionDate() {
        if (![1, 2].includes(this.paymentTypeValue)) { // PF et Loyer
            this.startDateInputElt.value = ''
            this.endDateInputElt.value = ''
            return null
        }

        this.checkStartDate()
        this.checkEndDate()
    }
    /**
     * Vérifie le montant à payer si redevance ou caution.
     */
    checkToPaidAmt() {
        if (isNaN(this.toPayAmtInputElt.value)) {
            this.isValid = false
            return this.formValidator.invalidField(this.toPayAmtInputElt, 'Valeur invalide.')
        }
        if ([1, 2, 10, 20].includes(this.paymentTypeValue) && !this.toPayAmtInputElt.value) { // PF, loyer, cautionn prêt
            this.isValid = false
            return this.formValidator.invalidField(this.toPayAmtInputElt, 'Saisie obligatoire.')
        }
        if (false === this.formValidator.checkAmount(this.toPayAmtInputElt, 0, 9999)) {
            this.isValid = false
        }
    }
    /**
     * Vérifie le montant restitué si Restitution caution.
     */
    checkReturnAmt() {
        if (isNaN(this.returnAmtInputElt.value)) {
            this.isValid = false
            return this.formValidator.invalidField(this.returnAmtInputElt, 'Valeur invalide.')
        }
        if (this.paymentTypeValue === 11 && !this.returnAmtInputElt.value) { // Restitution Caution
            this.isValid = false
            return this.formValidator.invalidField(this.returnAmtInputElt, 'Saisie obligatoire.')
        }
        if (false === this.formValidator.checkAmount(this.returnAmtInputElt, 0, 999)) {
            this.isValid = false
        }
    }
    /**
     * Vérifie la date du paiement.
     */
    checkPaymentDate() {
        const intervalWithNow = (new Date() - new Date(this.paymentDateInputElt.value)) / (1000 * 60 * 60 * 24)

        if (intervalWithNow < 0) {
            this.isValid = false
            return this.formValidator.invalidField(this.paymentDateInputElt, 'La date ne peut être postérieure à la date du jour.')
        }

        if (!this.paymentDateInputElt.value &&
            ((20 === this.paymentTypeValue || this.paidAmtInputElt.value > 0 || this.paymentTypeSelectElt.value)
                || (11 === this.paymentTypeValue || this.returnAmtInputElt.value > 0 || this.paymentTypeSelectElt.value))) {
            this.isValid = false
            return this.formValidator.invalidField(this.paymentDateInputElt, 'Saisie obligatoire.')
        }

        if (false === this.formValidator.checkDate(this.paymentDateInputElt, -(365 * 2), 0)) {
            this.isValid = false
        }
    }
    /**
     * Vérifie le moyen de paiement saisi.
     */
    checkPaymentMeans() {
        if (!this.paymentTypeSelectElt.value && (20 === this.paymentTypeValue || this.paymentDateInputElt.value
            || this.paidAmtInputElt.value > 0 || this.returnAmtInputElt.value > 0)) {
            this.isValid = false
            return this.formValidator.invalidField(this.paymentTypeSelectElt, 'Saisie obligatoire.')
        }
        return this.formValidator.validField(this.paymentTypeSelectElt)
    }
    /**
     * Vérifie le montant du paiement saisi.
     */
    checkPaidAmt() {
        if (isNaN(this.paidAmtInputElt.value)) {
            this.isValid = false
            return this.formValidator.invalidField(this.paidAmtInputElt, 'Valeur invalide.')
        }
        if ((!this.paidAmtInputElt.value && [1, 2, 10].includes(this.paymentTypeValue)
                && (this.paymentDateInputElt.value || this.paymentTypeSelectElt.value))
            || (!this.paidAmtInputElt.value && 30 === this.paymentTypeValue)) {
            this.isValid = false
            return this.formValidator.invalidField(this.paidAmtInputElt, 'Saisie obligatoire.')
        }
        if (false === this.formValidator.checkAmount(this.paidAmtInputElt, 0, 9999)) {
            this.isValid = false
        }
    }
    checkNoContribReason() {
        if (1 !== this.paymentTypeValue || null === this.noContribInputElt) {
            return null
        }
        if (this.noContribInputElt.checked === true && !this.noContribReasonSelectElt.value) {
            this.isValid = false
            return this.formValidator.invalidField(this.noContribReasonSelectElt, 'Saisie obligatoire.')
        }
        return this.formValidator.validField(this.noContribReasonSelectElt)
    }
    checkStartDate() {
        if (!this.startDateInputElt.value) {
            this.isValid = false
            return this.formValidator.invalidField(this.startDateInputElt, 'Saisie obligatoire.')
        }
        if (false === this.formValidator.checkDate(this.startDateInputElt, -(9 * 365), (3 * 31))) {
            this.isValid = false
        }
    }
    checkEndDate() {
        if (!this.endDateInputElt.value) {
            this.isValid = false
            return this.formValidator.invalidField(this.endDateInputElt, 'Saisie obligatoire.')
        } else if (this.endDateInputElt.value && new Date(this.endDateInputElt.value) <= new Date(this.startDateInputElt.value)) {
            this.isValid = false
            return this.formValidator.invalidField(this.endDateInputElt, 'La date doit être supérieure à la date de début.')
        }

        if (false === this.formValidator.checkDate(this.endDateInputElt, -(9 * 365), (3 * 31))) {
            this.isValid = false
        }
    }
}