import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import SelectType from '../utils/selectType'
import ValidationForm from '../utils/validationForm'
import ParametersUrl from '../utils/parametersUrl'
import { Modal } from 'bootstrap'

export default class SupportContributions {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.selectType = new SelectType()
        // this.validationDate = new ValidationDate()
        this.validationForm = new ValidationForm()
        this.parametersUrl = new ParametersUrl()
        this.modalElt = new Modal(document.getElementById('contribution-modal'))

        this.resourcesChecked = false // Ressources vérifiées dans la base de données
        this.resourcesAmt = null
        this.contributionAmt = null
        this.toPayAmt = null
        this.rentAmt = null

        this.btnNewElt = document.getElementById('js-new-contribution')
        this.contributionRate = parseFloat(this.btnNewElt.getAttribute('data-contribution-rate'))
        // this.supportStartDate = new Date(this.btnNewElt.getAttribute('data-support-start-date'))
        // this.supportEndDate = new Date(this.btnNewElt.getAttribute('data-support-end-date'))
        this.trElt = null

        this.modalConfirmElt = document.getElementById('modal-confirm')

        this.sumToPayAmtElt = document.querySelector('.js-sumToPayAmt')
        this.sumPaidAmtElt = document.querySelector('.js-sumPaidAmt')
        this.sumStillToPayAmtElt = document.querySelector('.js-sumStillToPayAmt')

        this.themeColor = document.getElementById('header').getAttribute('data-color')
        this.countContributionsElt = document.getElementById('count-contributions')
        this.nbTotalContributionsElt = document.getElementById('nb-total-contributions')
        this.supportId = document.getElementById('support').getAttribute('data-support')
        this.contributionId = null

        // Formulaire modal
        this.modalContributionElt = document.getElementById('contribution-modal')
        this.formContributionElt = this.modalContributionElt.querySelector('form[name=contribution]')
        this.typeSelect = document.getElementById('contribution_type')
        this.startDateInput = document.getElementById('contribution_startDate')
        this.endDateInput = document.getElementById('contribution_endDate')
        this.resourcesAmtInput = document.getElementById('contribution_resourcesAmt')
        this.rentAmtInput = document.getElementById('contribution_rentAmt')
        this.aplAmtInput = document.getElementById('contribution_aplAmt')
        this.toPayAmtInput = document.getElementById('contribution_toPayAmt')
        this.calculationMethodElt = document.getElementById('calculationMethod')
        this.paymentDateInput = document.getElementById('contribution_paymentDate')
        this.paymentTypeSelect = document.getElementById('contribution_paymentType')
        this.paidAmtInput = document.getElementById('contribution_paidAmt')
        this.stillToPayAmtInput = document.getElementById('contribution_stillToPayAmt')
        this.returnAmtInput = document.getElementById('contribution_returnAmt')
        this.commentInput = document.getElementById('contribution_comment')
        this.commentExportInput = document.getElementById('contribution_commentExport')
        this.infoContribElt = document.getElementById('js-info-contrib')

        this.blockExportElt = document.getElementById('js-block-export')
        this.pdfBtnElt = document.getElementById('js-btn-pdf')
        this.mailBtnElt = document.getElementById('js-btn-mail')
        this.deleteBtnElt = document.getElementById('modal-btn-delete')
        this.saveBtnElt = document.getElementById('js-btn-save')

        this.now = new Date()
        this.error = false

        this.init()
    }

    init() {
        this.btnNewElt.addEventListener('click', () => {
            if (this.loader.isActive() === false) {
                this.newContribution()
            }
        })

        document.querySelectorAll('tr.contribution').forEach(trElt => {
            const btnGetElt = trElt.querySelector('button.js-get')
            btnGetElt.addEventListener('click', () => {
                if (this.loader.isActive() === false) {
                    this.trElt = trElt
                    this.getContribution(Number(btnGetElt.getAttribute('data-id')))
                }
            })
            const btnDeleteElt = trElt.querySelector('button.js-delete')
            btnDeleteElt.addEventListener('click', () => {
                this.trElt = trElt
                this.modalConfirmElt.setAttribute('data-url', btnDeleteElt.getAttribute('data-url'))
            })

        })

        this.saveBtnElt.addEventListener('click', e => {
            e.preventDefault()
            if (this.loader.isActive() === false) {
                this.tryToSave()
            }
        })

        this.deleteBtnElt.addEventListener('click', e => {
            e.preventDefault()
            if (this.loader.isActive() === false) {
                this.deleteContribution(this.deleteBtnElt.href)
            }
        })

        this.modalConfirmElt.addEventListener('click', e => {
            e.preventDefault()
            this.ajax.send('GET', this.modalConfirmElt.getAttribute('data-url'), this.responseAjax.bind(this))
        })

        this.typeSelect.addEventListener('input', () => {
            this.initForm()
            this.checkType()
        })

        this.endDateInput.addEventListener('focusout', () => this.checkEndDateContribution())

        this.modalContributionElt.querySelectorAll('.js-money').forEach(elt => {
            elt.addEventListener('input', () => {
                this.checkMoney(elt)
            })
        })

        // Récupère les ressources et calcul le montant à payer du suivi au clic sur le bouton.
        document.getElementById('btn-update-contribution').addEventListener('click', e => {
            e.preventDefault()
            this.loader.on()
            if (this.resourcesChecked === false) {
                this.ajax.send('GET', this.btnNewElt.getAttribute('data-url'), this.responseAjax.bind(this))
            } else {
                this.getResources()
            }
        });

        [this.rentAmtInput, this.aplAmtInput].forEach(elt => {
            elt.addEventListener('input', () => {
                this.calculateAmountToPay()
            });
        });
            
        [this.toPayAmtInput, this.paidAmtInput].forEach(elt => {
            elt.addEventListener('input', () => {
                this.calculateAmountStillDue()
            });
        });

        this.paymentDateInput.addEventListener('focusout', () => {
            this.checkPaidAmt()
        });

        this.calculateSumAmts()

        const contributionId = Number(this.parametersUrl.get('contributionId'))
        this.trElt = document.getElementById('contribution-' + contributionId)
        if (this.trElt) {
            this.getContribution(contributionId)
        }

        this.pdfBtnElt.addEventListener('click', e => {
            e.preventDefault()
            window.open(this.pdfBtnElt.getAttribute('data-url').replace('__id__', this.contributionId));          
        })
            
        this.mailBtnElt.addEventListener('click', e => {
            e.preventDefault();
            if (window.confirm('Confirmer l\'envoi du reçu par email au suivi ?')) {
                const url = this.mailBtnElt.getAttribute('data-url').replace('__id__', this.contributionId);
                this.ajax.send('GET', url, this.responseAjax.bind(this));
            }
        })
    }

    /**
     * Vérifie le type de partipation (redevance ou caution).
     */
    checkType() {
        const option = this.selectType.getOption(this.typeSelect)

        // Masque tous les champs du formulaire.
        this.formContributionElt.querySelectorAll('.js-contrib').forEach(elt => {
            elt.classList.add('d-none')
        })

        // Si PF / Redevance et Loyer.
        if ([1, 2].includes(option)) {
            this.formContributionElt.querySelectorAll('.js-contribution').forEach(elt => {
                elt.classList.remove('d-none')
            })
        } else {
            this.calculationMethodElt.textContent = null
        }
        if (option === 2) {
            this.formContributionElt.querySelector('.js-rent').classList.remove('d-none')
        }
        if ([1, 2, 10, 30, 31, 32].includes(option)) { // Redevance, caution, remboursement
            this.formContributionElt.querySelectorAll('.js-payment').forEach(elt => {
                elt.classList.remove('d-none')
            })
        }
        if ([1, 2, 10].includes(option)) { // Redevance, loyer, caution
            this.formContributionElt.querySelector('.js-stillToPayAmt').classList.remove('d-none')
        }
        if ([1, 2, 10].includes(option)) { // Redevance, loyer, caution
            this.formContributionElt.querySelector('label[for="contribution_toPayAmt"]').textContent = 'Montant à régler'
        }
        if (option === 20) { // Prêt
            this.formContributionElt.querySelector('label[for="contribution_toPayAmt"]').textContent = 'Montant prêté'
        }
        if ([1, 2, 10, 20].includes(option)) { // Redevance, loyer, caution, prêt
            this.formContributionElt.querySelector('.js-toPayAmt').classList.remove('d-none')
        }
        if (option === 11) { // Restitution caution
            this.formContributionElt.querySelector('.js-returnAmt').classList.remove('d-none')
        }
        if (option >= 1) { // Tout sauf vide
            this.formContributionElt.querySelector('.js-paymentDate').classList.remove('d-none')
            this.formContributionElt.querySelector('.js-paymentType').classList.remove('d-none')
            this.formContributionElt.querySelector('.js-comment').classList.remove('d-none')
        }
    }

    /**
     * Calcul la somme de tous les montants pour le footer du tableau.
     */
    calculateSumAmts() {
        this.sumToPayAmtElt.textContent = this.getSumAmts(document.querySelectorAll('td.js-toPayAmt')).toLocaleString() + ' €'
        this.sumPaidAmtElt.textContent = this.getSumAmts(document.querySelectorAll('td.js-paidAmt')).toLocaleString() + ' €'

        const stillToPayAmtElts = document.querySelectorAll('td.js-stillToPayAmt')
        const sumStillToPayAmt = this.getSumAmts(stillToPayAmtElts)
        stillToPayAmtElts.forEach(elt => {
            this.changeTextColor(elt, elt.textContent)
        })
        this.sumStillToPayAmtElt.textContent = sumStillToPayAmt.toLocaleString() + ' €'
        this.changeTextColor(this.sumStillToPayAmtElt, sumStillToPayAmt)
    }

    /**
     * Donne le ratio de jours de présence dans le mois.
     */
    getRateDays() {
        return 1
        // const startDate = new Date(this.startDateInput.value)
        // const endDate = new Date(this.endDateInput.value)

        // const startMonthDate = new Date(startDate)
        // startMonthDate.setDate(1)
        // const endMonthDate = new Date(startDate)
        // endMonthDate.setMonth(startDate.getMonth() + 1)
        
        // const nbDaysInPeriod = this.getDiffDays(startDate, endDate)
        // const nbDaysInMonth = this.getDiffDays(startMonthDate, endMonthDate)
        // return nbDaysInPeriod / nbDaysInMonth
        // if (rateDays > 1 || rateDays < 0) {
        //     rateDays = 0
        // }
        // return rateDays
    }

    /**
     * 
     * @param {Date} date1 
     * @param {Date} date2 
     * @returns {Number}
     */
    getDiffDays(date1, date2) {
        const diffTime = Math.abs(date2 - date1)
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    }

    /**
     * Calcule le montant de la participation.
     */
    calculateAmountToPay() {
        const rateDays = this.getRateDays()
        let calculationMethod = ''
        // Si redevance/PF est fixé dans l'évaluation
        if (this.contributionAmt > 0) {
            this.toPayAmtInput.value = this.contributionAmt - this.aplAmtInput.value
            calculationMethod = 'Montant fixé dans l\'évalution sociale (' + this.contributionAmt + ' €)' +
                (this.aplAmtInput.value > 0 ? ' - Montant APL (' + this.aplAmtInput.value + ' €)' : '') + '.'
            // Sinon si loyer fixe à payer
        } else if (this.rentAmtInput.value > 0) {
            this.toPayAmtInput.value = (Math.round(this.rentAmtInput.value * rateDays * 100) / 100) - this.aplAmtInput.value
            calculationMethod = 'Montant du loyer (' + this.rentAmtInput.value + ' €)' +
                (rateDays < 1 ? ' x Prorata présence sur le mois (' + (Math.round(rateDays * 10000) / 100) + ' %)' : '') +
                (this.aplAmtInput.value > 0 ? ' - Montant APL (' + this.aplAmtInput.value + ' €).' : '.')
        // Sinon détermine le montant en fonction des ressources saisies
        } else if (!isNaN(this.resourcesAmtInput.value) && !isNaN(this.contributionRate)) {
            this.toPayAmtInput.value = Math.round(this.resourcesAmtInput.value * this.contributionRate * rateDays)
            calculationMethod = 'Montant des ressources (' + this.resourcesAmtInput.value +
                ' €) x Taux de participation (' + (this.contributionRate * 100) + ' %)' + (rateDays < 1 ? ' x Prorata présence sur le mois (' +
                    (Math.round(rateDays * 10000) / 100) + ' %).' : '.')
        }
        this.calculationMethodElt.textContent = 'Mode de calcul : ' + calculationMethod
    }

    /**
     * Calcule le restant dû.
     */
    calculateAmountStillDue() {
        if (!isNaN(this.toPayAmtInput.value) && !isNaN(this.paidAmtInput.value)) {
            this.stillToPayAmtInput.value = Math.round((this.toPayAmtInput.value - this.paidAmtInput.value) * 100) / 100
        }
        this.changeTextColor(this.stillToPayAmtInput, this.stillToPayAmtInput.value)
    }

    /**
     * Retourne vrai si le formualaire est valide.
     */
    isValidForm() {
        this.error = false
        const option = this.selectType.getOption(this.typeSelect)

        this.checkContributionType(option)
        this.checkContributionDate(option)
        this.checkToPaidAmt(option)
        this.checkReturnAmt(option)
        this.checkPaymentDate(option)
        this.checkPaymentType(option)
        this.checkPaidAmt(option)

        return this.error != true
    }

    /**
     * Vérifie le type de contribution.
     * @param {Number} option 
     */
    checkContributionType(option) {
        if (!option) {
            this.error = true
            return this.validationForm.invalidField(this.typeSelect, 'Saisie obligatoire.')
        }

        return this.validationForm.validField(this.typeSelect)
    }

    /**
     * Vérifie la date de contribution.
     * @param {Number} option 
     */
    checkContributionDate(option) {
        if ([1, 2].includes(option)) { // PF et Loyer
            if (!this.startDateInput.value) {
                this.error = true
                this.validationForm.invalidField(this.startDateInput, 'Saisie obligatoire.')
            } else {
                this.validationForm.validField(this.startDateInput)
            }
            if (!this.endDateInput.value) {
                this.error = true
                this.validationForm.invalidField(this.endDateInput, 'Saisie obligatoire.')
            } else {
                this.validationForm.validField(this.endDateInput)
            }
            this.checkEndDateContribution()
        } else {
            this.startDateInput.value = ''
            this.endDateInput.value = ''
        }
    }

    checkEndDateContribution() {
        if (this.endDateInput.value && new Date(this.endDateInput.value) <= new Date(this.startDateInput.value)) {
            this.validationForm.invalidField(this.endDateInput, 'Date antérieure au début de la période.')
        } else {
            this.validationForm.validField(this.endDateInput)
        }
    }

    /**
     * Vérfifie le montant à payer si redevance ou caution.
     * @param {Number} option 
     */
    checkToPaidAmt(option) {
        if (isNaN(this.toPayAmtInput.value)) {
            this.error = true
            return this.validationForm.invalidField(this.toPayAmtInput, 'Valeur invalide.')
        }
        if ([1, 2, 10, 20].includes(option) && !this.toPayAmtInput.value) { // PF, loyer, cautionn prêt
            this.error = true
            return this.validationForm.invalidField(this.toPayAmtInput, 'Saisie obligatoire.')
        }
    }

    /**
     * Vérifie le montant restitué si Restitution caution.
     * @param {Number} option 
     */
    checkReturnAmt(option) {
        if (isNaN(this.returnAmtInput.value)) {
            this.error = true
            return this.validationForm.invalidField(this.returnAmtInput, 'Valeur invalide.')
        }
        if (option == 11 && !this.returnAmtInput.value) { // Restitution Caution
            this.error = true
            return this.validationForm.invalidField(this.returnAmtInput, 'Saisie obligatoire.')
        }
    }

    /**
     * Vérifie le montant du paiement saisi.
     * @param {Number} option 
     */
    checkPaidAmt(option) {
        if (isNaN(this.paidAmtInput.value)) {
            this.error = true
            return this.validationForm.invalidField(this.paidAmtInput, 'Valeur invalide.')
        }
        if ((!this.paidAmtInput.value && [1, 2, 10].includes(option) && (this.paymentDateInput.value || this.selectType.getOption(this.paymentTypeSelect))) ||
            (!this.paidAmtInput.value && [30, 31, 32].includes(option))) {
            this.error = true
            return this.validationForm.invalidField(this.paidAmtInput, 'Saisie obligatoire.')
        }
        return this.validationForm.validField(this.paidAmtInput)
    }

    /**
     * Vérifie la date du paiement.
     * @param {Number} option 
     */
    checkPaymentDate(option) {
        const intervalWithNow = (this.now - new Date(this.paymentDateInput.value)) / (1000 * 60 * 60 * 24)

        if ((this.paymentDateInput.value && !intervalWithNow) || intervalWithNow > (365 * 19)) {
            this.error = true
            return this.validationForm.invalidField(this.paymentDateInput, 'Date invalide.')
        }
        if (intervalWithNow < 0) {
            this.error = true
            return this.validationForm.invalidField(this.paymentDateInput, 'La date ne peut être postérieure à la date du jour.')
        }
        if (!this.paymentDateInput.value && (option === 20 || this.paidAmtInput.value || this.selectType.getOption(this.paymentTypeSelect) || this.returnAmtInput.value)) {
            this.error = true
            return this.validationForm.invalidField(this.paymentDateInput, 'La date ne peut pas être vide.')
        }
        return this.validationForm.validField(this.paymentDateInput)
    }

    /**
     * Vérifie le type de paiement saisi.
     * @param {Number} option 
     */
    checkPaymentType(option) {
        if (!this.selectType.getOption(this.paymentTypeSelect) && (option === 20 || this.paymentDateInput.value || this.paidAmtInput.value || this.returnAmtInput.value)) {
            this.error = true
            return this.validationForm.invalidField(this.paymentTypeSelect, 'Saisie obligatoire.')
        }
        return this.validationForm.validField(this.paymentTypeSelect)
    }


    /**
     * Affiche un formulaire modal vierge.
     */
    newContribution() {
        this.contributionId = null
        this.modalElt.show()
        this.selectType.setOption(this.typeSelect, '')
        this.initForm()
        this.checkType()
        this.modalContributionElt.querySelector('form').action = '/support/' + this.supportId + '/contribution/new'
        this.deleteBtnElt.classList.replace('d-block', 'd-none')
        this.saveBtnElt.textContent = 'Enregistrer'
        this.blockExportElt.classList.replace('d-block', 'd-none')
    }

    /**
     * Requête pour obtenir le RDV sélectionné dans le formulaire modal.
     * @param {String} id 
     */
    getContribution(id) {
        this.loader.on()

        this.contributionId = id
        this.modalContributionElt.querySelector('form').action = `/contribution/${id}/edit`

        this.deleteBtnElt.classList.replace('d-none', 'd-block')
        this.deleteBtnElt.href = this.deleteBtnElt.getAttribute('data-url').replace('__id__', id);
        this.saveBtnElt.textContent = 'Mettre à jour'

        this.initForm()
        this.checkType()

        this.ajax.send('GET', '/contribution/' + id + '/get', this.responseAjax.bind(this))
    }

    /**
     * Réinitialise le formulaire.
     */
    initForm() {
        this.validationForm.reinit()
        this.formContributionElt.querySelectorAll('input').forEach(inputElt => {
            if (inputElt.type != 'hidden') {
                inputElt.value = null
            }
        })
        this.selectType.setOption(this.paymentTypeSelect, '')
        this.commentInput.value = ''
        this.commentExportInput.value = ''
        this.infoContribElt.innerHTML = ''

        this.blockExportElt.classList.replace('d-block', 'd-none')
    }

    /**
     * Sélectionne une des options dans une liste select.
     * @param {HTMLElement} selectElt 
     * @param {Number} value 
     */
    selectOption(selectElt, value) {
        selectElt.querySelectorAll('option').forEach(option => {
            if (parseInt(option.value) === value) {
                option.selected = true
            } else {
                option.selected = false
            }
        })
    }

    /**
     * Enregistre l'opération.
     */
    tryToSave() {
        this.loader.on()
        if (this.isValidForm()) {
            this.ajax.send('POST', this.formContributionElt.getAttribute('action'), this.responseAjax.bind(this), new FormData(this.formContributionElt))
        } else {
            new MessageFlash('danger', 'Veuillez corriger le(s) erreur(s) avant d\'enregistrer.')
            this.loader.off()
        }
    }

    /**
     * Envoie une requête ajax pour supprimer l 'enregistrement.
     * @param {String} url 
     */
    deleteContribution(url) {
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
            case 'get_resources':
                this.getResources(response.data)
                break
            case 'show':
                this.showContribution(response.data)
                break
            case 'create':
                this.createContribution(response.data.contribution)
                new MessageFlash(response.alert, response.msg)
                break
            case 'update':
                this.updateContribution(response.data.contribution)
                new MessageFlash(response.alert, response.msg)
                break
            case 'delete':
                this.trElt.remove()
                this.updateCounts(-1)
                this.loader.off()
                this.modalElt.hide()
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
     * Donne le montant des ressources du ménage.
     * @param {Array} data 
     */
    getResources(data = null) {
        if (this.resourcesChecked === false) {
            this.resourcesAmt = data.resourcesAmt
            this.contributionAmt = data.contributionAmt
            this.toPayAmt = data.toPayAmt
            this.rentAmt = data.rentAmt
            this.resourcesChecked = true
        }

        this.resourcesAmtInput.value === '' ? this.resourcesAmtInput.value = this.resourcesAmt : null
        // this.contributionAmt = this.contributionAmt
        this.toPayAmtInput.value = this.toPayAmt
        this.rentAmtInput.value = this.rentAmt

        this.calculateAmountToPay()
        this.loader.off()
    }

    /**
     * Donne la redevance sélectionnée dans le formulaire modal.
     * @param {Array} data 
     */
    showContribution(data) {
        const contribution = data.contribution
        this.modalElt.show()
        this.selectOption(this.typeSelect, contribution.type)
        this.startDateInput.value = contribution.startDate ? contribution.startDate.substring(0, 10) : null
        this.endDateInput.value = contribution.endDate ? contribution.endDate.substring(0, 10) : null
        this.resourcesAmtInput.value = contribution.resourcesAmt
        this.rentAmtInput.value = contribution.rentAmt
        this.toPayAmtInput.value = contribution.toPayAmt
        this.paymentDateInput.value = contribution.paymentDate ? contribution.paymentDate.substring(0, 10) : null
        this.selectOption(this.paymentTypeSelect, contribution.paymentType)
        this.paidAmtInput.value = contribution.paidAmt
        this.stillToPayAmtInput.value = Math.round(contribution.stillToPayAmt * 100) / 100
        this.returnAmtInput.value = contribution.returnAmt
        this.commentInput.value = contribution.comment
        this.commentExportInput.value = contribution.commentExport

        this.infoContribElt.innerHTML = this.getInfoContribElt(data)

        this.checkType()
        if (contribution.id) {
            this.blockExportElt.classList.replace('d-none', 'd-block')
        }
        this.loader.off()
    }

    /**  
     * Donnes les informations sur l'enregistrement (date de création, créateur...).
     * @param {data} data
     */
    getInfoContribElt(data) {
        const contribution = data.contribution
        let htmlContent = `Créé le ${this.formatDatetime(contribution.createdAt)} par ${data.createdBy}`
        if (contribution.createdAt != contribution.updatedAt) {
            htmlContent = htmlContent + `<br/> (modifié le ${this.formatDatetime(contribution.updatedAt)} par ${data.updatedBy})`
        }
        return htmlContent
    }

    /**
     * Crée la ligne de la nouvelle redevance dans le tableau.
     * @param {Array} data 
     */
    createContribution(data) {
        const contributionElt = document.createElement('tr')
        contributionElt.className = 'js-payment'

        contributionElt.innerHTML = this.getPrototypeContribution(data)

        const containerContributionsElt = document.getElementById('container-contributions')
        containerContributionsElt.insertBefore(contributionElt, containerContributionsElt.firstChild)
        this.updateCounts(1)

        this.calculateSumAmts()

        const btnGetElt = contributionElt.querySelector('button.js-get')
        btnGetElt.addEventListener('click', () => {
            if (this.loader.isActive() === false) {
                this.trElt = contributionElt
                this.getContribution(Number(btnGetElt.getAttribute('data-id')))
            }
        })

        const btnDeleteElt = contributionElt.querySelector('button.js-delete')
        btnDeleteElt.addEventListener('click', () => {
            this.trElt = contributionElt
            this.modalConfirmElt.setAttribute('data-url', btnDeleteElt.getAttribute('data-url'))
        })
        this.loader.off()
    }

    /**
     * Met à jour la ligne du tableau correspondant au contribution.
     * @param {Object} contribution 
     */
    updateContribution(contribution) {
        this.trElt.querySelector('td.js-type').textContent = contribution.typeToString + (contribution.type == 11 ? ' (' + this.formatMoney(contribution.returnAmt) + ')' : '')
        this.trElt.querySelector('td.js-startDate').textContent = this.formatDatetime(contribution.startDate, 'date') + ' - ' + this.formatDatetime(contribution.endDate, 'date')
        this.trElt.querySelector('td.js-toPayAmt').textContent = this.formatMoney(contribution.toPayAmt)
        this.trElt.querySelector('td.js-paidAmt').textContent = this.formatMoney(contribution.paidAmt)
        this.trElt.querySelector('td.js-stillToPayAmt').textContent = this.formatMoney(this.roundMoney(contribution.stillToPayAmt))
        this.trElt.querySelector('td.js-paymentDate').textContent = this.formatDatetime(contribution.paymentDate, 'date')
        this.trElt.querySelector('td.js-paymentType').textContent = contribution.paymentTypeToString
        this.trElt.querySelector('td.js-comment').textContent = this.sliceComment((contribution.comment ?? '')  + " \n" + (contribution.commentExport ?? ''))
        this.calculateSumAmts()
        this.loader.off()
    }

    /**
     * Crée la ligne de la contribution.
     * @param {Object} contribution 
     */
    getPrototypeContribution(contribution) {
        return `
            <td scope='row' class='align-middle text-center'>
                <button class='btn btn-${this.themeColor} btn-sm shadow js-get' data-id='${contribution.id}' 
                    data-url='/contribution/${contribution.id}/get' data-toggle='tooltip' 
                    data-placement='bottom' title='Voir l'enregistrement'><span class='fas fa-eye'></span>
                </button>
            </td>
            <td class='align-middle js-type'>${contribution.typeToString}<br/>
                <span class='text-secondary'>${contribution.type == 11 ? ' (' + this.formatMoney(contribution.returnAmt) + ')' : '' }</span>
            </td>
            <td class='align-middle js-startDate'>${this.formatDatetime(contribution.startDate, 'date') + ' - ' + this.formatDatetime(contribution.endDate, 'date')}</td>
            <td class='align-middle text-right js-toPayAmt'>${this.formatMoney(contribution.toPayAmt)}</td>
            <td class='align-middle text-right js-paidAmt'>${this.formatMoney(contribution.paidAmt)}</td>
            <td class='align-middle text-right js-stillToPayAmt'>${this.formatMoney(this.roundMoney(contribution.stillToPayAmt))}</td>
            <td class='align-middle text-center js-paymentDate'>${this.formatDatetime(contribution.paymentDate, 'date')}</td>
            <td class='align-middle js-paymentType'>${contribution.paymentType ? contribution.paymentTypeToString : ''}</td>
            <td class='align-middle js-comment'>${this.sliceComment((contribution.comment ?? '')  + " \n" + (contribution.commentExport ?? ''))}</td>
            <td class='align-middle js-createdAt'>${this.formatDatetime(this.now, 'date')}</td>
            <td class="align-middle text-center js-pdfGenerate">
                <span><i class="fas fa-file-pdf text-secondary fa-lg"></i></span>
            </td>
            <td class="align-middle text-center js-mailSent">
                <span><i class="fas fa-envelope text-secondary fa-lg"></i></span>
            </td>
            <td class='align-middle text-center'>
                <button data-url='/contribution/${contribution.id}/delete' 
                    class='js-delete btn btn-danger btn-sm shadow my-1' data-placement='bottom' 
                        title='Supprimer l'enregistrement' data-toggle='modal' data-target='#modal-block'>
                    <span class='fas fa-trash-alt'></span>
                </button>
            </td>`
    }

    /**
     * Arrondi un nombre en valeur monétaire.
     * @param {Number} number 
     */
    roundMoney(number) {
        return number ? Math.round(number * 100) / 100 : ''
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
        this.countContributionsElt.textContent = parseInt(this.countContributionsElt.textContent) + value
        if (this.nbTotalContributionsElt) {
            this.nbTotalContributionsElt.textContent = parseInt(this.nbTotalContributionsElt.textContent) + value
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
            return this.validationForm.validField(moneyElt)
        }
        return this.validationForm.invalidField(moneyElt, 'Valeur invalide.')
    }

    /**
     * Vérifie si la date est valide.
     * @param {inputElt} dateElt 
     */
    checkDate(dateElt) {
        const interval = Math.round((this.now - new Date(dateElt.value)) / (1000 * 60 * 60 * 24))
        if ((dateElt.value && !Number.isInteger(interval)) || interval > (365 * 99) || interval < -(365 * 99)) {
            return this.validationForm.invalidField(dateElt, 'Date invalide.')
        }
        return this.validationForm.validField(dateElt)
    }

    /**
     * Donne la somme des montants.
     * @param {HTMLElement} elts 
     */
    getSumAmts(elts) {
        // const amounts = []
        let sumAmts = 0
        elts.forEach(elt => {
            let amount = elt.textContent
            if (amount) {
                // amounts.push(parseFloat(amount))
                sumAmts += parseFloat(amount.replace(' ', '').replace(',', '.'))
            }
        })
        // const sumAmts = amounts.reduce((a, b) => a + b, 0)
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