import AbstractForm from '../utils/form/AbstractForm'
import PaymentManager from './PaymentManager'
import FieldDisplayer from "../utils/form/fieldDisplayer"
import ContributionCalculator from "./ContributionCalculator"

export default class PaymentForm extends AbstractForm
{
    /**
     * @param {PaymentManager} manager
     */
     constructor(manager) {
        super(manager)

        this.infoPaymentElt = document.querySelector('[data-payment="info"]')
        this.typeSelectElt = document.getElementById('payment_type')
        this.startDateInputElt = document.getElementById('payment_startDate')
        this.endDateInputElt = document.getElementById('payment_endDate')
        this.resourcesAmtInputElt = document.getElementById('payment_resourcesAmt')
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

        this.btnEditElts = this.formElt.querySelectorAll('button[data-edit]')

        new ContributionCalculator(this.formElt, (payment) => this.afterCalculContribution(payment))

        this.error = true
        this.displayedFields = []

        this.init()
    }

    init() {
        document.querySelectorAll('div[data-parent-field]')
            .forEach(elt => this.displayedFields.push(new FieldDisplayer(elt)))

        this.modalElt.querySelectorAll('input[data-amount]').forEach(elt => {
            elt.addEventListener('input', () => this.checkAmount(elt, 0, 9999))
        })

        this.typeSelectElt.addEventListener('input', () => this.checkType())
        this.paymentDateInputElt.addEventListener('focusout', () => this.checkPaymentDate())
        this.resourcesAmtInputElt.addEventListener('input', () => this.checkResources())
        this.startDateInputElt.addEventListener('focusout', () => this.checkStartDate())
        this.endDateInputElt.addEventListener('focusout', () => this.checkEndDate())
        this.toPayAmtInputElt.addEventListener('input', () => this.calculateAmountStillDue())
        this.paidAmtInputElt.addEventListener('input', () => this.calculateAmountStillDue())

        this.formElt.querySelector('button[data-action="export_pdf"]').addEventListener('click', e => {
            e.preventDefault()
            this.manager.requestExportPdf()
        })
        
        this.formElt.querySelector('button[data-action="send_email"]').addEventListener('click', e => {
            e.preventDefault()
            this.manager.requestSendEmail()
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

    new() {
        this.resetForm()

        this.infoPaymentElt.textContent = this.getCreateUpdateInfo()

        this.checkType()
        this.checkResources()

        this.modalElt.querySelector('#btn_show_calcul_contribution').classList.add('d-none')
        this.btnEditElts.forEach(elt => elt.classList.add('d-none'))

        this.formData = new FormData(this.formElt)
    }


    /**
     * Show payment in form.
     * 
     * @param {Object} payment
     */
     show(payment) {
        this.initForm(payment)

        this.infoPaymentElt.innerHTML = this.getCreateUpdateInfo(payment)

        this.checkType()
        this.checkResources()

        this.btnEditElts.forEach(elt => elt.classList.remove('d-none'))

        this.formData = new FormData(this.formElt)
    }

    /**
     * @param {Event} e 
     */
     requestToSave(e) {
        e.preventDefault()

        this.formElt.classList.add('was-validated')

        if (this.loader.isActive() === false && this.isValidForm()) {
            this.ajax.send( 'POST', this.formElt.action, this.responseAjax, new FormData(this.formElt))
        }
    }

    /**
     * Vérifie le type (redevance ou caution).
     */
    checkType() {
        this.paymentTypeValue = parseInt(this.typeSelectElt.value)

        // Masque tous les champs du formulaire.
        this.formElt.querySelectorAll('[data-payment]').forEach(elt => {
            elt.classList.add('d-none')
        })
        if ([1, 2, 10].includes(this.paymentTypeValue)) { // Redevance, loyer, caution
            this.formElt.querySelector('label[for="payment_toPayAmt"]').textContent = 'Montant à régler'
        }
        if (this.paymentTypeValue === 20) { // Prêt
            this.formElt.querySelector('label[for="payment_toPayAmt"]').textContent = 'Montant prêté'
        }

        this.displayedFields.forEach(displayedField => displayedField.check())
    }

    checkResources() {
        const noContribDivElt = this.formElt.querySelector('div[data-type="no_contrib"]')

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
     * 
     * @return {Boolean}
     */
    isValidForm() {
        this.error = false
        this.paymentTypeValue = parseInt(this.typeSelectElt.value)

        this.checkContributionDate()
        this.checkToPaidAmt()
        this.checkReturnAmt()
        this.checkPaymentDate()
        this.checkPaymentMeans()
        this.checkPaidAmt()
        this.checkNoContribReason()

        if (this.isValid() === false) {
            this.error = true
        }

        return this.error === false
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
        this.formHydrator.hydrate(payment)

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
        this.manager.changeTextColor(this.stillToPayAmtInputElt, this.stillToPayAmtInputElt.value)
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
            this.error = true

            return this.invalidField(this.toPayAmtInputElt, 'Valeur invalide.')
        }
        if ([1, 2, 10, 20].includes(this.paymentTypeValue) && !this.toPayAmtInputElt.value) { // PF, loyer, cautionn prêt
            this.error = true

            return this.invalidField(this.toPayAmtInputElt, 'Saisie obligatoire.')
        }
        if (this.checkAmount(this.toPayAmtInputElt, 0, 9999) === false) {
            this.error = true
        }
    }

    /**
     * Vérifie le montant restitué si Restitution caution.
     */
    checkReturnAmt() {
        if (isNaN(this.returnAmtInputElt.value)) {
            this.error = true

            return this.invalidField(this.returnAmtInputElt, 'Valeur invalide.')
        }
        if (this.paymentTypeValue === 11 && !this.returnAmtInputElt.value) { // Restitution Caution
            this.error = true

            return this.invalidField(this.returnAmtInputElt, 'Saisie obligatoire.')
        }
        if (this.checkAmount(this.returnAmtInputElt, 0, 999) === false) {
            this.error = true
        }
    }

    /**
     * Vérifie la date du paiement.
     */
    checkPaymentDate() {
        const intervalWithNow = (new Date() - new Date(this.paymentDateInputElt.value)) / (1000 * 60 * 60 * 24)

        if (intervalWithNow < 0) {
            this.error = true

            return this.invalidField(this.paymentDateInputElt, 'La date ne peut être postérieure à la date du jour.')
        }

        if (!this.paymentDateInputElt.value &&
            ((this.paymentTypeValue === 20 || this.paidAmtInputElt.value > 0 || this.paymentTypeSelectElt.value)
                || (this.paymentTypeValue === 11 || this.returnAmtInputElt.value > 0 || this.paymentTypeSelectElt.value))
        ) {
            this.error = true

            return this.invalidField(this.paymentDateInputElt, 'Saisie obligatoire.')
        }

        if (this.isValidDate(this.paymentDateInputElt, -(365 * 2), 0) === false) {
            this.error = true
        }
    }

    /**
     * Vérifie le moyen de paiement saisi.
     */
    checkPaymentMeans() {
        if (!this.paymentTypeSelectElt.value && (this.paymentTypeValue === 20 || this.paymentDateInputElt.value
            || this.paidAmtInputElt.value > 0 || this.returnAmtInputElt.value > 0)
        ) {
            this.error = true
            
            return this.invalidField(this.paymentTypeSelectElt, 'Saisie obligatoire.')
        }

        return this.validField(this.paymentTypeSelectElt)
    }

    /**
     * Vérifie le montant du paiement saisi.
     */
    checkPaidAmt() {
        if (isNaN(this.paidAmtInputElt.value)) {
            this.error = true

            return this.invalidField(this.paidAmtInputElt, 'Valeur invalide.')
        }
        if ((!this.paidAmtInputElt.value && [1, 2, 10].includes(this.paymentTypeValue)
                && (this.paymentDateInputElt.value || this.paymentTypeSelectElt.value))
            || (!this.paidAmtInputElt.value && this.paymentTypeValue === 30)
        ) {
            this.error = true

            return this.invalidField(this.paidAmtInputElt, 'Saisie obligatoire.')
        }
        if (this.checkAmount(this.paidAmtInputElt, 0, 9999) === false) {
            this.error = true
        }
    }

    checkNoContribReason() {
        if (this.paymentTypeValue !== 1 || this.noContribInputElt === null) {
            return null
        }
        if (this.noContribInputElt.checked === true && !this.noContribReasonSelectElt.value) {
            this.error = true

            return this.invalidField(this.noContribReasonSelectElt, 'Saisie obligatoire.')
        }

        return this.validField(this.noContribReasonSelectElt)
    }

    checkStartDate() {
        if (!this.startDateInputElt.value) {
            this.error = true

            return this.invalidField(this.startDateInputElt, 'Saisie obligatoire.')
        }
        if (this.isValidDate(this.startDateInputElt, -(9 * 365), (3 * 31)) === false) {
            this.error = true
        }
    }

    checkEndDate() {
        if (!this.endDateInputElt.value) {
            this.error = true

            return this.invalidField(this.endDateInputElt, 'Saisie obligatoire.')
        } else if (this.endDateInputElt.value && new Date(this.endDateInputElt.value) <= new Date(this.startDateInputElt.value)) {
            this.error = true

            return this.invalidField(this.endDateInputElt, 'La date doit être supérieure à la date de début.')
        }

        if (this.isValidDate(this.endDateInputElt, -(9 * 365), (3 * 31)) === false) {
            this.error = true
        }
    }
}