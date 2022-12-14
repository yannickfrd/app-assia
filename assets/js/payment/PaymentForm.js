import PaymentManager from './PaymentManager'
import FormValidator from "../utils/form/formValidator";
import ParametersUrl from "../utils/parametersUrl";
import AlertMessage from "../utils/AlertMessage";
import FieldDisplayer from "../utils/form/fieldDisplayer";
import ContributionCalcul from "./ContributionCalcul";

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
        this.modalPaymentElt = document.getElementById('payment_modal')
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
        this.infoPaymentDivElt = document.getElementById('js_info_payment')

        this.pdfBtnElt = this.formPaymentElt.querySelector('button[data-action="create_pdf"]')
        this.mailBtnElt = this.formPaymentElt.querySelector('button[data-action="send_email"]')
        this.deleteBtnElt = this.formPaymentElt.querySelector('button[data-action="delete"]')
        this.saveBtnElt = this.formPaymentElt.querySelector('button[data-action="save"]')
        this.editBtnElts = this.formPaymentElt.querySelectorAll('button[data-edit]')

        this.confirmBtnElt = document.getElementById('modal-confirm')

        this.parametersUrl = new ParametersUrl()
        this.formValidator = new FormValidator(this.formPaymentElt)
        this.contributionCalcul = new ContributionCalcul(this.formPaymentElt, this.afterCalculContribution.bind(this))

        this.payment = null
        this.isValid = true
        this.displayedFields = []

        this.init()
    }

    init() {
        // If paymentId is in url "get"
        const paymentId = this.parametersUrl.get('paymentId')
        this.paymentManager.trElt = document.getElementById('payment-' + paymentId)
        document.querySelectorAll('table#table_payments tbody tr')
            .forEach(trElt => {
                if (trElt.id === 'payment-'+paymentId) {
                    this.requestShowPayment(paymentId)
                }
            })

        document.querySelectorAll('div[data-parent-field]')
            .forEach(elt => this.displayedFields.push(new FieldDisplayer(elt)))

        this.modalPaymentElt.querySelectorAll('input[data-amount]')
            .forEach(elt => elt
                .addEventListener('input', () => this.formValidator.checkAmount(elt, 0, 9999)))

        this.typeSelectElt.addEventListener('input', () => {
            this.initForm()
            this.checkType()
        })

        this.paymentDateInputElt.addEventListener('focusout', () => this.checkPaymentDate())
        this.resourcesAmtInputElt.addEventListener('input', () => this.checkResources())

        this.startDateInputElt.addEventListener('focusout', () => this.checkStartDate())
        this.endDateInputElt.addEventListener('focusout', () => this.checkEndDate())

        this.toPayAmtInputElt.addEventListener('input', () => this.calculateAmountStillDue())
        this.paidAmtInputElt.addEventListener('input', () => this.calculateAmountStillDue())

        this.saveBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.tryToSave()
        })

        this.pdfBtnElt.addEventListener('click', e => {
            e.preventDefault()
            window.open(this.pdfBtnElt.dataset.path.replace('__id__', this.payment.id))
        })
        
        this.mailBtnElt.addEventListener('click', e => {
            e.preventDefault()
            if (window.confirm('Confirmer l\'envoi du re??u par email au suivi ?')) {
                this.ajax.send('GET', this.mailBtnElt.dataset.path.replace('__id__',  this.payment.id), this.responseAjax)
            }
        })

        this.deleteBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.paymentManager.confirmModal.show()
            this.confirmBtnElt.dataset.path = this.deleteBtnElt.dataset.path.replace('__id__',  this.payment.id)
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

    /**
     * Enregistre l'op??ration.
     */
    tryToSave() {
        if (!this.loader.isActive()) {
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

    requestShowPayment(paymentId) {
        if (!this.loader.isActive()) {
            this.loader.on()

            const path = document.querySelector('table#table_payments').dataset.pathShow.replace('__id__', paymentId)
            this.ajax.send('GET', path, this.responseAjax)
        }
    }

    /**
     * Show payment in form.
     * @param {Object} payment
     */
    showPayment(payment) {
        this.payment = payment
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

        const noContribCheckboxElt = this.formPaymentElt.querySelector('#payment_noContrib')
        if (noContribCheckboxElt) {
            noContribCheckboxElt.checked = payment.noContrib
            this.formPaymentElt.querySelector('#payment_noContribReason').value = payment.noContribReason ?? ''
        }

        this.formPaymentElt.querySelector('#payment_contributionRate').value = payment.contributionRate
        this.formPaymentElt.querySelector('#payment_nbConsumUnits').value = payment.nbConsumUnits

        this.infoPaymentDivElt.innerHTML = this.getInfoPaymentElt(payment)
        this.checkType()
        this.checkResources()
        if (payment.id) {
            this.editBtnElts.forEach(elt => {
                elt.classList.remove('d-none')
            })
        }
    }

    newPayment() {
        this.typeSelectElt.value = ''
        this.initForm()
        this.checkType()
        this.deleteBtnElt.classList.replace('d-block', 'd-none')
        this.saveBtnElt.querySelector('span').textContent = 'Enregistrer'
        this.modalPaymentElt.querySelector('form').action = this.paymentManager.btnNewElt.dataset.path
        document.getElementById('show_calcul_contribution_btn').classList.add('d-none')
        this.editBtnElts.forEach(elt => {
            elt.classList.add('d-none')
        })
    }

    /**
     * Donnes les informations sur l'enregistrement (date de cr??ation, cr??ateur...).
     * @param {Object} payment
     */
    getInfoPaymentElt(payment) {
        let htmlContent = `Cr???? le ${this.paymentManager.formatDatetime(payment.createdAt)} par ${payment.createdBy.fullname}`
        if (payment.createdAt !== payment.updatedAt) {
            htmlContent = htmlContent + `<br/> (modifi?? le ${this.paymentManager.formatDatetime(payment.updatedAt)} par ${payment.updatedBy.fullname})`
        }
        return htmlContent
    }

    initForm() {
        this.formValidator.reinit()
        this.formPaymentElt.querySelectorAll('input, textarea').forEach(inputElt => {
            if (inputElt.type !== 'hidden') {
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
        this.editBtnElts.forEach(elt => elt.classList.add('d-none'))
    }

    /**
     * Requ??te pour obtenir le RDV s??lectionn?? dans le formulaire modal.
     * @param {String} id
     */
    getPayment(id) {
        this.loader.on()

        this.formPaymentElt.action = this.formPaymentElt.dataset.path.replace('__id__', id)

        this.deleteBtnElt.classList.replace('d-none', 'd-block')
        this.saveBtnElt.querySelector('span').textContent = 'Mettre ?? jour'

        this.initForm()
        this.checkType()

        this.ajax.send('GET', '/payment/' + id + '/show', this.responseAjax)
    }

    /**
     * V??rifie le type (redevance ou caution).
     */
    checkType() {
        this.paymentTypeValue = parseInt(this.typeSelectElt.value)

        // Masque tous les champs du formulaire.
        this.formPaymentElt.querySelectorAll('[data-payment]').forEach(elt => {
            elt.classList.add('d-none')
        })
        if ([1, 2, 10].includes(this.paymentTypeValue)) { // Redevance, loyer, caution
            this.formPaymentElt.querySelector('label[for="payment_toPayAmt"]').textContent = 'Montant ?? r??gler'
        }
        if (20 === this.paymentTypeValue) { // Pr??t
            this.formPaymentElt.querySelector('label[for="payment_toPayAmt"]').textContent = 'Montant pr??t??'
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
     * Calcule le restant d??.
     */
    calculateAmountStillDue() {
        if (!isNaN(this.toPayAmtInputElt.value) && !isNaN(this.paidAmtInputElt.value)) {
            this.stillToPayAmtInputElt.value = Math.round((this.toPayAmtInputElt.value - this.paidAmtInputElt.value) * 100) / 100
        }
        this.paymentManager.changeTextColor(this.stillToPayAmtInputElt, this.stillToPayAmtInputElt.value)
    }

    /**
     * V??rifie le type de paiement.
     */
    checkPaymentType() {
        if (!this.paymentTypeValue) {
            this.isValid = false
            return this.formValidator.invalidField(this.typeSelectElt, 'Saisie obligatoire.')
        }

        this.formValidator.validField(this.typeSelectElt)
    }
    /**
     * V??rifie la date de paiement.
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
     * V??rifie le montant ?? payer si redevance ou caution.
     */
    checkToPaidAmt() {
        if (isNaN(this.toPayAmtInputElt.value)) {
            this.isValid = false
            return this.formValidator.invalidField(this.toPayAmtInputElt, 'Valeur invalide.')
        }
        if ([1, 2, 10, 20].includes(this.paymentTypeValue) && !this.toPayAmtInputElt.value) { // PF, loyer, cautionn pr??t
            this.isValid = false
            return this.formValidator.invalidField(this.toPayAmtInputElt, 'Saisie obligatoire.')
        }
        if (false === this.formValidator.checkAmount(this.toPayAmtInputElt, 0, 9999)) {
            this.isValid = false
        }
    }
    /**
     * V??rifie le montant restitu?? si Restitution caution.
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
     * V??rifie la date du paiement.
     */
    checkPaymentDate() {
        const intervalWithNow = (new Date() - new Date(this.paymentDateInputElt.value)) / (1000 * 60 * 60 * 24)

        if (intervalWithNow < 0) {
            this.isValid = false
            return this.formValidator.invalidField(this.paymentDateInputElt, 'La date ne peut ??tre post??rieure ?? la date du jour.')
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
     * V??rifie le moyen de paiement saisi.
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
     * V??rifie le montant du paiement saisi.
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
            return this.formValidator.invalidField(this.endDateInputElt, 'La date doit ??tre sup??rieure ?? la date de d??but.')
        }

        if (false === this.formValidator.checkDate(this.endDateInputElt, -(9 * 365), (3 * 31))) {
            this.isValid = false
        }
    }
}