import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import ParametersUrl from '../utils/parametersUrl'
import ApiCalendar from '../api/ApiCalendar';
import RdvModel from "./model/RdvModel";
import RdvForm from "./RdvForm";

export default class CalendarManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.rdvForm = new RdvForm(this)
        this.parametersUrl = new ParametersUrl()
        this.apiCalendar = new ApiCalendar()

        this.calendarContainer = document.getElementById('calendar-container')
        this.newRdvBtn = document.getElementById('js-new-rdv')
        this.dayElts = document.querySelectorAll('.calendar-day-block')
        this.rdvElts = document.querySelectorAll('.calendar-event')
        this.fullWidthCheckbox = document.getElementById('full-width');
        this.showWeekendCheckbox = document.getElementById('show-weekend');
        this.weekendElts = document.querySelectorAll('div[data-weekend=true]')

        this.modalRdvElt = document.getElementById('modal-rdv')

        this.rdvStartInput = this.modalRdvElt.querySelector('#rdv_start')
        this.rdvEndInput = this.modalRdvElt.querySelector('#rdv_end')
        this.dateInput = this.modalRdvElt.querySelector('#date')
        this.startInput = this.modalRdvElt.querySelector('#start')
        this.endInput = this.modalRdvElt.querySelector('#end')
        this.btnDeleteElt = this.modalRdvElt.querySelector('#modal-btn-delete')

        this.themeColor = document.getElementById('header').dataset.color

        this.showWeekendsItem = localStorage.getItem('calendar.show_weekends')
        this.fullWidthItem = localStorage.getItem('calendar.full_width')

        this.init()
    }

    init() {
        this.newRdvBtn.addEventListener('click', e => this.rdvForm.resetForm(e))

        this.dayElts.forEach(dayElt => {
            this.hideRdvElts(dayElt)
            dayElt.addEventListener('click', e => this.newRdv(e, dayElt))
        })

        this.rdvElts.forEach(rdvElt => {
            rdvElt.addEventListener('click', e => this.getRdv(e, rdvElt))
        })

        this.dateInput.addEventListener('focusout', this.checkDate.bind(this))
        this.startInput.addEventListener('input', this.checkStart.bind(this))
        this.endInput.addEventListener('focusout', this.checkEnd.bind(this))

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

        // Si l'ID d'un suivi est en paramètre, affiche le rendez-vous
        const rdvElt = document.getElementById('rdv-' + this.parametersUrl.get('rdv_id'))
        if (rdvElt) {
            this.rdvForm.requestShowRdv(rdvElt.href)
        }
    }

    /**
     * On click in edit rdv.
     * @param {Event} e
     * @param {HTMLLinkElement} rdvElt
     */
    getRdv(e, rdvElt) {
        e.preventDefault()
        this.rdvForm.requestShowRdv(rdvElt.href)
    }

    /**
     * On click on rdv in day.
     * @param {Event} e
     * @param {HTMLElement} dayElt
     */
    newRdv(e, dayElt) {
        this.rdvForm.resetForm(e)
        this.dateInput.value = dayElt.id
        this.modalRdvElt.querySelector('#rdv_start').value = dayElt.id + 'T00:00'
        this.modalRdvElt.querySelector('#rdv_end').value = dayElt.id + 'T00:00'
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
     * Vérifie si la date est valide.
     */
    checkDate() {
        this.updateDateTimes()
    }

    /**
     * Vérifie si l'heure de début est valide.
     */
    checkStart() {
        if (isNaN(this.startInput.value)) {
            const endHour = parseInt(this.startInput.value.substr(0, 2)) + 1

            this.endInput.value = endHour.toString().padStart(2, '0') + ':' + this.startInput.value.substr(3, 2)
            this.updateDateTimes()
        }
    }

    /**
     * Vérifie si l'heure de fin est valide.
     */
    checkEnd() {
        this.updateDateTimes()
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
     * Donne la réponse à la requête Ajax.
     * @param {Object} data
     */
    responseAjax(data) {
        if (data.action) {
            switch (data.action) {
                case 'show':
                    this.showRdv(data.rdv, data.canEdit);
                    break;
                case 'create':
                    this.createRdv(data);
                    break;
                case 'edit':
                    this.updateRdv(data);
                    break;
                case 'delete':
                    this.deleteRdv(data.rdvId, data.apiUrls);
                    break;
            }
        }
        if (data.msg) {
            new MessageFlash(data.alert, data.msg)
        }
        this.loader.off()
    }

    /**
     * Affiche le RDV dans le formulaire modal.
     * @param {Object} rdv
     * @param {boolean} canEdit
     */
    showRdv(rdv, canEdit) {
        this.rdvForm.show(rdv, canEdit)
    }

    /**
     * Crée le RDV dans le container du jour de l'agenda.
     * @param {Object} data
     */
    createRdv(data) {
        const rdv =  data.rdv
        const apiUrls =  data.apiUrls
        const rdvElt = document.createElement('a')
        rdvElt.href = this.rdvForm.getPathEditRdv()
            .replace('__id__', rdv.id)
            .replace('edit', 'show')
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

        rdvElt.addEventListener('click', e => this.getRdv(e, rdvElt))

        //v1
        if (data.action === 'create') {
            this.apiCalendar.addEvent(new RdvModel(rdv), apiUrls)
        }
        // v2 ...
        // this.apiCalendar.execute(action, apiUrls)

        this.rdvForm.closeModal()
    }

    /**
     * Met à jour le RDV dans l'agenda.
     * @param {Object} data
     */
    updateRdv(data) {
        this.rdvForm.updateApiRdv(data.rdv, data.apiUrls)

        document.getElementById('rdv-'+data.rdv.id).remove()
        this.createRdv(data)
    }

    /**
     * Supprime le RDV dans l'agenda.
     * @param {number} rdvId
     * @param {Object} apiUrls
     */
    deleteRdv(rdvId, apiUrls) {
        const rdvElt = document.getElementById('rdv-' + rdvId)
        rdvElt.remove()
        // this.hideRdvElts(dayElt)

        this.apiCalendar.execute('delete', apiUrls)

        this.rdvForm.closeModal()
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
