import Calendar from "./calendar";
import RdvManager from "./RdvManager";
import FormValidator from "../utils/form/formValidator";
import SelectManager from "../utils/form/SelectManager";
import DateFormater from "../utils/date/dateFormater";
import MessageFlash from "../utils/messageFlash";

export default class RdvForm {

    /**
     * @param {RdvManager|Calendar} manager
     */
    constructor(manager) {
        this.manager = manager

        this.loader = manager.loader
        this.ajax = manager.ajax
        this.themeColor = manager.themeColor

        this.modalRdvElt = document.getElementById('modal-rdv')
        this.btnCreateRdvElt = this.modalRdvElt.querySelector('button[data-action="create-rdv"]')
        this.btnDeleteRdvElt = this.modalRdvElt.querySelector('button[data-action="delete-rdv"]')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name="rdv"]')
        this.rdvTitleElt = this.modalRdvElt.querySelector('.modal-header h2')

        this.counterRdvsElt = document.querySelector('span#count-rdvs')

        this.infoRdvElt = document.getElementById('js-rdv-info')
        this.rdvTitleInput = this.modalRdvElt.querySelector('input[name="rdv[title]"]')
        this.rdvStartInput = this.modalRdvElt.querySelector('input[name="rdv[start]"]')
        this.rdvEndInput = this.modalRdvElt.querySelector('input[name="rdv[end]"]')
        this.rdvLocationInput = this.modalRdvElt.querySelector('input[name="rdv[location]"]')
        this.rdvStatusInput = this.modalRdvElt.querySelector('select[name="rdv[status]"]')
        this.rdvContentText = this.modalRdvElt.querySelector('textarea[name="rdv[content]"]')

        const divSupportElt = document.querySelector('div[data-support]')
        this.supportId = divSupportElt ? divSupportElt.dataset.support : null

        this.supportSelectElt = document.getElementById('rdv_supportGroup')

        this.dateInput = this.modalRdvElt.querySelector('input[name="date"]')
        this.startInput = this.modalRdvElt.querySelector('input[name="start"]')
        this.endInput = this.modalRdvElt.querySelector('input[name="end"]')

        this.currentUserId = document.getElementById('user-name').dataset.userId

        this.formValidator = new FormValidator(this.modalRdvElt)

        const eventObject = { name: 'onModal', elementId: 'modal-rdv' }
        this.usersSelectManager = new SelectManager('#rdv_users', eventObject, { width: '100%' })
        this.tagsSelectManager = new SelectManager('#rdv_tags', eventObject)

        this.init()
    }

    init() {
        this.btnCreateRdvElt.addEventListener('click', e => this.requestSaveRdv(e))
    }

    resetForm(e) {
        this.formValidator.reinit()

        this.rdvTitleElt.textContent = 'Nouveau rendez-vous'

        const dateFormater = new DateFormater()
        this.dateInput.value = dateFormater.getDateNow()
        this.startInput.value = dateFormater.getHour()
        const end = parseInt(this.startInput.value.substr(0, 2)) + 1
        this.endInput.value = end + ':00'

        this.infoRdvElt.innerHTML = ''
        this.rdvStartInput.value = ''
        this.rdvTitleInput.value = ''
        this.rdvEndInput.value = ''
        this.rdvLocationInput.value = ''
        this.rdvStatusInput.value = ''
        this.rdvContentText.value = ''

        this.supportSelectElt.value = this.supportId ?? ''
        this.supportSelectElt.disabled = this.supportId !== null

        this.usersSelectManager.updateSelect(this.currentUserId)

        this.btnDeleteRdvElt.classList.add('d-none')

        if (e !== undefined && (e.target.className && e.target.className.search('calendar-event') !== 0)) {
            this.tagsSelectManager.clearSelect()
            this.modalRdvElt.show()
            const rdvTags = $('#rdv_tags')
            rdvTags.val(null).trigger('change')
        }
    }

    requestSaveRdv() {
        if (this.rdvTitleInput.value === '') {
            return new MessageFlash('danger', 'Le rdv est vide.')
        }

        if (!this.loader.isActive()) {
            this.updateDateTimes()

            this.loader.on()

            const formData = new FormData(this.formRdvElt)
            this.ajax.send(
                'POST',
                this.formRdvElt.action,
                this.manager.responseAjax.bind(this.manager),
                formData
            )
        }
    }


    /**
     * Met à jour les dates de début et de fin.
     */
    updateDateTimes() {
        if (isNaN(this.dateInput.value) && isNaN(this.startInput.value)) {
            this.rdvStartInput.value = this.dateInput.value + 'T' + this.startInput.value
        }
        if (isNaN(this.dateInput.value) && isNaN(this.endInput.value)) {
            this.rdvEndInput.value = this.dateInput.value + 'T' + this.endInput.value
        }
    }


    /**
     * @param {Object} rdv
     * @returns {string}
     */
    createTags(rdv)
    {
        let tags = ''
        rdv.tags.forEach(tag => {
            tags += `<span class="badge bg-${tag.color} text-light mr-1">${tag.name}</span>`
        })

        return tags
    }

    updateCounterTasks(value) {
        const countRdvs = parseInt(this.counterRdvsElt.dataset.countRdvs) + value
        this.counterRdvsElt.dataset.countTasks = countRdvs
        this.counterRdvsElt.textContent = countRdvs.toLocaleString()
    }
}