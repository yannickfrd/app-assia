import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import DateFormater from '../utils/date/dateFormater'
import {Modal} from 'bootstrap'
import ParametersUrl from '../utils/parametersUrl'
import SelectManager from '../utils/form/SelectManager'
import ApiCalendar from '../api/ApiCalendar';
import RdvModel from "./model/RdvModel";

export default class Calendar {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.parametersUrl = new ParametersUrl()
        this.modalElt = new Modal(document.getElementById('modal-rdv'))
        this.updateModalElt = new Modal(document.getElementById('modal-update'))
        this.apiCalendar = new ApiCalendar()

        this.calendarContainer = document.getElementById('calendar-container')
        this.newRdvBtn = document.getElementById('js-new-rdv')
        this.dayElts = document.querySelectorAll('.calendar-day-block')
        this.rdvElts = document.querySelectorAll('.calendar-event')
        this.fullWidthCheckbox = document.getElementById('full-width');
        this.showWeekendCheckbox = document.getElementById('show-weekend');
        this.weekendElts = document.querySelectorAll('div[data-weekend=true]')

        this.modalRdvElt = document.getElementById('modal-rdv')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name=rdv]')
        this.rdvTitleElt = this.modalRdvElt.querySelector('#js-rdv-title')
        this.infoRdvElt = document.getElementById('js-rdv-info')
        this.rdvStartInput = this.modalRdvElt.querySelector('#rdv_start')
        this.rdvEndInput = this.modalRdvElt.querySelector('#rdv_end')
        this.dateInput = this.modalRdvElt.querySelector('#date')
        this.startInput = this.modalRdvElt.querySelector('#start')
        this.endInput = this.modalRdvElt.querySelector('#end')
        this.rdvLocationInput = this.modalRdvElt.querySelector('#rdv_location')
        this.rdvStatusInput = this.modalRdvElt.querySelector('#rdv_status')
        this.rdvContentElt = this.modalRdvElt.querySelector('#rdv_content')
        this.btnSaveElt = this.modalRdvElt.querySelector('#js-btn-save')
        this.btnCancelElt = this.modalRdvElt.querySelector('#js-btn-cancel')
        this.btnDeleteElt = this.modalRdvElt.querySelector('#modal-btn-delete')

        this.googleCalendarCheckbox = this.modalRdvElt.querySelector('input[name="rdv[_googleCalendar]"]')
        this.outlookCalendarCheckbox = this.modalRdvElt.querySelector('input[name="rdv[_outlookCalendar]"]')

        const divSupportElt = document.querySelector('div[data-support]')
        this.supportId = divSupportElt ? divSupportElt.dataset.support : null

        this.themeColor = document.getElementById('header').dataset.color
        this.supportElt = document.getElementById('support')
        this.supportPeopleElt = document.getElementById('js-support-people')
        this.supportSelectElt = document.getElementById('rdv_supportGroup')

        this.showWeekendsItem = localStorage.getItem('calendar.show_weekends')
        this.fullWidthItem = localStorage.getItem('calendar.full_width')

        this.tagsSelectManager = new SelectManager(
            '#rdv_tags',
            { name: 'onModal', elementId: this.modalRdvElt.id }
        )
        this.usersSelectManager = new SelectManager(
            '#rdv_users',
            { name: 'onModal', elementId: 'modal-rdv' }
        )

        this.currentUserId = document.getElementById('user-name').dataset.userId

        this.rdvBeforeUpdate = null

        this.init()
    }

    init() {
        this.newRdvBtn.addEventListener('click', e => {
            this.resetData(e);
        })

        this.dayElts.forEach(dayElt => {
            this.hideRdvElts(dayElt)
            dayElt.addEventListener('click', e => {
                this.resetData(e)
                this.dateInput.value = dayElt.id
                this.modalRdvElt.querySelector('#rdv_start').value = dayElt.id + 'T00:00'
                this.modalRdvElt.querySelector('#rdv_end').value = dayElt.id + 'T00:00'
            })
        })

        this.rdvElts.forEach(rdvElt => {
            rdvElt.addEventListener('click', e => {
                this.resetData(e)
                this.requestGetRdv(rdvElt)
            })
        })

        this.dateInput.addEventListener('focusout', this.checkDate.bind(this))
        this.startInput.addEventListener('input', this.checkStart.bind(this))
        this.endInput.addEventListener('focusout', this.checkEnd.bind(this))

        this.btnSaveElt.addEventListener('click', e => this.requestSaveRdv(e))

        this.btnDeleteElt.addEventListener('click', e => {
            e.preventDefault()
            this.requestDeleteRdv()
        })

        if (this.fullWidthItem === 'true') {
            this.fullWidthCheckbox.checked = 'checked'
        }
        this.changeWidthCalendar();

        this.fullWidthCheckbox.addEventListener('click', () => this.changeWidthCalendar())

        if (this.showWeekendsItem === 'true') {
            this.showWeekendCheckbox.checked = 'checked'
        } else {
            this.hideWeekends();
        }

        this.showWeekendCheckbox.addEventListener('click', () => this.hideWeekends())

        // Si l'ID d'une suivi est en pramètre, affiche le rendez-vous
        const rdvElt = document.getElementById('rdv-' + this.parametersUrl.get('rdv_id'))
        if (rdvElt) {
            rdvElt.click()
        }

        if (localStorage.getItem('calendar.google') === 'true') {
            this.googleCalendarCheckbox.checked = 'checked'
        }
        if (localStorage.getItem('calendar.outlook') === 'true') {
            this.outlookCalendarCheckbox.checked = 'checked';
        }
    }

    /**
     * Modifie la largeur de l'agenda.
     */
    changeWidthCalendar() {
        if (this.fullWidthCheckbox.checked) {
            this.calendarContainer.classList.replace('container', 'container-fluid');
            localStorage.setItem('calendar.full_width', true);
        } else {
            this.calendarContainer.classList.replace('container-fluid', 'container');
            localStorage.setItem('calendar.full_width', false);
        }
    }
    /**
     * Masque ou affiche les week-ends.
     */
    hideWeekends() {
        this.weekendElts.forEach(elt => {
            elt.classList.toggle('d-none');
        });
        if (this.showWeekendCheckbox.checked) {
            localStorage.setItem('calendar.show_weekends', true);
        } else {
            localStorage.setItem('calendar.show_weekends', false);
        }
    }

    /**
     * Réinitialise le formulaire modal de rdv.
     * @param {Event} e
     */
    resetData(e) {
        e.preventDefault()
        if (this.supportElt) {
            this.modalRdvElt.querySelector('form').action = '/support/' + this.supportElt.dataset.support + '/rdv/new'
            this.modalRdvElt.querySelector('#rdv_title').value = this.supportPeopleElt.querySelector('.btn').textContent
        } else {
            this.modalRdvElt.querySelector('form').action = '/rdv/new'
            this.modalRdvElt.querySelector('#rdv_title').value = ''
        }
        this.rdvTitleElt.textContent = 'Rendez-vous'

        const dateFormater = new DateFormater()
        this.dateInput.value = dateFormater.getDateNow()
        this.startInput.value = dateFormater.getHour()
        const end = parseInt(this.startInput.value.substr(0, 2)) + 1
        this.endInput.value = end + ':00'

        this.infoRdvElt.innerHTML = ''
        this.rdvStartInput.value = ''
        this.rdvEndInput.value = ''
        this.rdvLocationInput.value = ''
        this.rdvStatusInput.value = ''

        this.supportSelectElt.value = this.supportId ?? ''
        this.supportSelectElt.disabled = this.supportId !== null

        this.usersSelectManager.updateSelect(this.currentUserId)

        this.modalRdvElt.querySelector('#rdv_content').value = ''
        this.btnDeleteElt.classList.add('d-none')
        this.btnSaveElt.classList.remove('d-none')

        if (e.target.className && e.target.className.search('calendar-event') !== 0) {
            this.tagsSelectManager.clearSelect()
            this.modalElt.show()
            const rdvTags = $('#rdv_tags')
            rdvTags.val(null).trigger('change')
        }
    }

    /**
     * Vérifie si la date est valide.
     */
    checkDate() {
        this.updateDatetimes()
    }

    /**
     * Vérifie si l'heure de début est valide.
     */
    checkStart() {
        if (isNaN(this.startInput.value)) {
            const endHour = parseInt(this.startInput.value.substr(0, 2)) + 1

            this.endInput.value = endHour.toString().padStart(2, '0') + ':' + this.startInput.value.substr(3, 2)
            this.updateDatetimes()
        }
    }

    /**
     * Vérifie si l'heure de fin est valide.
     */
    checkEnd() {
        this.updateDatetimes()
    }

    /**
     * Met à jour les dates de début et de fin.
     */
    updateDatetimes() {
        if (isNaN(this.dateInput.value) && isNaN(this.startInput.value)) {
            this.rdvStartInput.value = this.dateInput.value + 'T' + this.startInput.value
        }
        if (isNaN(this.dateInput.value) && isNaN(this.endInput.value)) {
            this.rdvEndInput.value = this.dateInput.value + 'T' + this.endInput.value
        }
    }

    /**
     * Requête pour obtenir le RDV sélectionné dans le formulaire modal.
     * @param {HTMLElement} rdvElt
     */
    requestGetRdv(rdvElt) {
        this.loader.on()
        this.rdvElt = rdvElt
        this.rdvId = Number(this.rdvElt.id.replace('rdv-', ''))
        this.ajax.send('GET', '/rdv/' + this.rdvId + '/show', this.responseAjax.bind(this))
    }

    /**
     * Requête pour sauvegarder le RDV.
     * @param {Event} e
     */
    requestSaveRdv(e) {
        e.preventDefault()

        if (this.modalRdvElt.querySelector('#rdv_title').value === '') {
            return new MessageFlash('danger', 'Le rdv est vide.')
        }

        if (!this.loader.isActive()) {
            this.updateDatetimes()

            // if (this.formRdvElt.elements['rdv__googleCalendar'].checked) {
            //     this.googleCalendarCheckbox = this.formRdvElt.elements['rdv__googleCalendar'].checked
            // }

            this.loader.on()

            this.ajax.send(
                'POST',
                this.formRdvElt.getAttribute('action'),
                this.responseAjax.bind(this), new FormData(this.formRdvElt)
            )
        }
    }

    /**
     * Requête pour supprimer le RDV.
     */
    requestDeleteRdv() {
        if (window.confirm('Voulez-vous vraiment supprimer ce rendez-vous ?')) {
            this.loader.on()
            this.ajax.send('GET', this.btnDeleteElt.href, this.responseAjax.bind(this))
        }
    }

    /**
     * Donne la réponse à la requête Ajax.
     * @param {Object} data
     */
    responseAjax(data) {
        if (data.action) {
            switch (data.action) {
                case 'show':
                    this.showRdv(data.rdv);
                    break;
                case 'create':
                    this.createRdv(data.rdv, data.action, data.apiUrls);
                    break;
                case 'update':
                    this.updateRdv(data.rdv, data.action, data.apiUrls);
                    break;
                case 'delete':
                    this.deleteRdv(data.rdv, data.apiUrls);
                    break;
            }
        }
        if (data.msg) {
            this.modalElt.hide()
            new MessageFlash(data.alert, data.msg)
        }
        this.loader.off()
    }

    /**
     * Affiche le RDV dans le formulaire modal.
     * @param {Object} rdv
     */
    showRdv(rdv) {
        this.rdvBeforeUpdate = rdv.getRdv

        this.modalRdvElt.querySelector('form').action = '/rdv/' + this.rdvId + '/edit'
        this.modalRdvElt.querySelector('#rdv_title').value = rdv.title
        this.rdvStartInput.value = rdv.start
        this.rdvEndInput.value = rdv.end

        this.dateInput.value = rdv.start.substr(0, 10)
        this.startInput.value = rdv.start.substr(11, 5)
        this.endInput.value = rdv.end.substr(11, 5)

        this.rdvLocationInput.value = rdv.location
        this.rdvStatusInput.value = rdv.status ? rdv.status : ''
        this.modalRdvElt.querySelector('#rdv_content').value = rdv.content ? rdv.content : ''

        this.infoRdvElt.innerHTML = this.getInfoRdvElt(rdv)

        const title = 'RDV' + (rdv.fullnameSupport ? ' | ' + rdv.fullnameSupport : '')
        this.rdvTitleElt.textContent = title

        if (rdv.supportId) {
            const href = this.rdvTitleElt.dataset.url.replace('__id__', rdv.supportId)
            this.rdvTitleElt.innerHTML = `<a href="${href}" class="text-${this.themeColor}" title="Accéder au suivi">${title}</a>`
        }

        if (rdv.canEdit) {
            this.btnDeleteElt.href = `/rdv/${this.rdvId}/delete`
            this.btnDeleteElt.classList.remove('d-none')
            this.btnSaveElt.classList.remove('d-none')
        } else {
            this.btnDeleteElt.classList.add('d-none')
            this.btnSaveElt.classList.add('d-none')
        }

        const userIds = []
        rdv.getRdv.users.forEach(user => userIds.push(user.id))
        this.usersSelectManager.updateSelect(userIds)

        this.supportSelectElt.value = ''
        this.supportSelectElt.disabled = rdv.getRdv.supportGroup !== null
        if (rdv.getRdv.supportGroup) {
            this.supportSelectElt.value = rdv.getRdv.supportGroup.id
            if (this.supportSelectElt.value === '') {
                const optionElt = document.createElement('option')
                optionElt.value = rdv.getRdv.supportGroup.id
                optionElt.textContent = rdv.getRdv.supportGroup.header.fullname
                this.supportSelectElt.appendChild(optionElt)
                this.supportSelectElt.value = rdv.getRdv.supportGroup.id
            }
        }

        this.modalElt.show()

        this.initTagSelect(rdv)
    }

    /**
     * Permet d'initialiser les valeurs dans le select multiple.
     * @param {Object} rdv
     */
    initTagSelect(rdv){
        const tagOptions = this.modalRdvElt.querySelectorAll('select#rdv_tags option')

        const listTagId = []
        rdv.tags.forEach(tag => {
            tagOptions.forEach(option => {
                if (parseInt(option.value) === parseInt(tag.id)){
                    listTagId.push(option.value)
                }
            })
        })
        this.tagsSelectManager.showOptionsFromArray(listTagId)
    }

    /**
     * Donnes les informations sur l'enregistrement (date de création, créateur...).
     * @param {Object} rdv
     */
    getInfoRdvElt(rdv) {
        let htmlContent = `Créé le ${rdv.createdAt} par ${rdv.createdBy}`
        if (rdv.createdAt !== rdv.updatedAt) {
            htmlContent = htmlContent + `<br/> (modifié le ${rdv.updatedAt} par ${rdv.updatedBy})`
        }
        return htmlContent
    }

    /**
     * Crée le RDV dans le container du jour de l'agenda.
     * @param {Object} rdv
     * @param {string} action
     * @param {Object} apiUrls
     */
    createRdv(rdv, action, apiUrls) {
        const rdvElt = document.createElement('div')
        rdvElt.className = `calendar-event bg-${this.themeColor} text-light`
        rdvElt.id = `rdv-${rdv.id}`
        rdvElt.dataset.title = 'Voir le rendez-vous'

        const title = this.modalRdvElt.querySelector('#rdv_title').value

        const rdvTime = () => {
            const rdvDate = new Date(rdv.start)
            const min = rdvDate.getMinutes().toString().length === 1 ? '0' + rdvDate.getMinutes() : rdvDate.getMinutes()

            return rdvDate.getHours() + ':' + min
        }

        rdvElt.innerHTML = rdv.day ? rdv.start + ' ' + title : rdvTime() + ' ' + rdv.title
        const dayElt = rdv.day ? document.getElementById(rdv.day) : document.getElementById(rdv.start.substr(0, 10))

        if (dayElt) {
            dayElt.insertBefore(rdvElt, dayElt.lastChild)
            this.sortDayBlock(dayElt)
            this.hideRdvElts(dayElt)
        }

        rdvElt.addEventListener('click', this.requestGetRdv.bind(this, rdvElt))

        //v1
        if (action === 'create') {
            this.apiCalendar.addEvent(new RdvModel(rdv), apiUrls)
        }
        // v2 ...
        // this.apiCalendar.execute(action, apiUrls)
    }

    /**
     * Met à jour le RDV dans l'agenda.
     * @param {Object} rdv
     * @param {string} action
     * @param {Object} apiUrls
     */
    updateRdv(rdv, action, apiUrls) {
        const rdvModel = new RdvModel(rdv.getRdv)

        if ((this.googleCalendarCheckbox.checked && null === this.rdvBeforeUpdate.googleEventId)
            || (this.outlookCalendarCheckbox.checked && null === this.rdvBeforeUpdate.outlookEventId)
            || (rdvModel.isDifferent(this.rdvBeforeUpdate) && (this.googleCalendarCheckbox.checked
                || this.outlookCalendarCheckbox.checked))
        ) {
            this.updateModalElt.show()

            const listApis = () => {
                let list = {}

                if (this.googleCalendarCheckbox.checked) {
                    list.google = apiUrls.google;
                }
                if (this.outlookCalendarCheckbox.checked) {
                    list.outlook =  apiUrls.outlook
                }

                return Object.keys(list).length === 0 ? apiUrls : list
            }

            document.getElementById('modal-confirm').addEventListener('click', () => {
                this.apiCalendar.addEvent(rdvModel, listApis())
            }, {once: true})
        }

        this.rdvElt.remove()
        this.createRdv(rdv, action, apiUrls)
    }

    /**
     * Supprime le RDV dans l'agenda.
     */
    deleteRdv() {
        const rdvElt = document.getElementById('rdv-' + this.rdvId)
        const dayElt = rdvElt.parentNode

        rdvElt.remove()
        this.hideRdvElts(dayElt)

        // this.apiCalendar.execute('delete', apiUrls)
    }

    /**
     * Tri les événements du jour.
     * @param {HTMLElement} dayElt
     */
    sortDayBlock(dayElt) {

        const rdvArr = []
        dayElt.querySelectorAll('.calendar-event').forEach(rdvElt => {
            rdvArr.push(rdvElt)
        })

        rdvArr.sort((a, b) => a.innerText > b.innerText ? 1 : -1)
            .map(node => dayElt.appendChild(node))
    }

    /**
     * Cache les RDV en fonction de la hauteur du container.
     * @param {HTMLElement} dayElt
     */
    hideRdvElts(dayElt) {

        const rdvElts = dayElt.querySelectorAll('.calendar-event')

        const othersEventsElt = dayElt.querySelector('.calendar-others-events')
        if (othersEventsElt) {
            othersEventsElt.remove()
        }

        const maxHeight = (dayElt.clientHeight - 24) / 21.2

        dayElt.querySelectorAll('a').forEach(divElt => {
            divElt.classList.remove('d-none')
        })

        let sumHeightdivElts = 44
        dayElt.querySelectorAll('a').forEach(divElt => {
            const styles = window.getComputedStyle(divElt)
            sumHeightdivElts = sumHeightdivElts + divElt.clientHeight + parseFloat(styles['marginTop']) + parseFloat(styles['marginBottom'])
            if (sumHeightdivElts > dayElt.clientHeight && rdvElts.length > maxHeight) {
                divElt.classList.add('d-none')
            }
        })

        if (sumHeightdivElts > dayElt.clientHeight && rdvElts.length > maxHeight) {
            const divElt = document.createElement('a')
            divElt.className = 'calendar-others-events bg-' + this.themeColor + ' text-light font-weight-bold'
            let date = dayElt.id.replace('-', '/')
            date = date.replace('-', '/')
            divElt.href = '/calendar/day/' + date
            divElt.dataset.title = 'Voir tous les rendez-vous du jour'
            divElt.textContent = (parseInt(rdvElts.length - maxHeight) + 2) + ' autres...'
            dayElt.insertBefore(divElt, dayElt.lastChild)
        }
    }
}
