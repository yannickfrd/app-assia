import AbstractManager from '../../AbstractManager'
import RdvForm from './RdvForm'
import ApiCalendar from '../../api/ApiCalendar'
import AlertMessage from '../../utils/AlertMessage'

export default class CalendarManager extends AbstractManager {

    constructor() {
        super('rdv', '#container_events', {backdrop: 'static', keyboard: false})

        this.form = new RdvForm(this)
        this.apiCalendar = new ApiCalendar(this.form)

        this.checkboxFullWidth = document.getElementById('full-width')
        this.checkboxShowWeekend = document.getElementById('show-weekend')
        this.weekendElts = document.querySelectorAll('div[data-weekend=true]')

        this.sectionContainerElt = document.querySelector('section>.container')
        this.showWeekendsItem = localStorage.getItem('calendar.show_weekends')
        this.fullWidthItem = localStorage.getItem('calendar.full_width')

        this.#init()
    }

    #init() {
        document.querySelectorAll('.calendar-day-block').forEach(dayElt => {
            this.#hideRdvElts(dayElt)

            dayElt.addEventListener('click', e => {
                if (e.target.dataset.action === 'new_rdv') {
                    this.new(e)
                }
            })
        })

        if (this.fullWidthItem === 'true') {
            this.checkboxFullWidth.checked = 'checked'
        }

        this.#editWidthCalendar()

        this.checkboxFullWidth.addEventListener('click', () => this.#editWidthCalendar())

        if (this.showWeekendsItem === 'true') {
            this.checkboxShowWeekend.checked = 'checked'
        } else {
            this.#hideWeekends()
        }

        this.checkboxShowWeekend.addEventListener('click', () => this.#hideWeekends())
    }

    /**
     * Action after the ajax response.
     * 
     * @param {Object} response
     */
    responseAjax(response) {
        this.checkActions(response, response.rdv)
        this.apiCalendar.checkResponse(response)

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        this.objectModal.hide()
    }

    extraUpdatesElt(rdv, rdvElt) {
        const dayElt = document.getElementById(rdv.day ?? rdv.start.substr(0, 10))

        if (!dayElt) {
            return this.deleteElt(rdv)
        }

        rdvElt.querySelector('[data-object-key="start"]').textContent = rdv.start.substr(11, 5)

        dayElt.insertBefore(rdvElt, dayElt.lastChild)

        this.#sortDayContainer(dayElt)
    }

    /**
     * Sort the events in the day container.
     * 
     * @param {HTMLElement} dayElt
     */
    #sortDayContainer(dayElt) {
        const rdvArr = []
        dayElt.querySelectorAll('[data-rdv-id]').forEach(eventElt => {
            rdvArr.push(eventElt)
        })

        rdvArr.sort((a, b) => a.innerText > b.innerText ? 1 : -1)
            .map(node => dayElt.appendChild(node))

        this.#hideRdvElts(dayElt)
    }

    /**
     * Hide the events if to many in the day container (height).
     * 
     * @param {HTMLElement} dayElt
     */
    #hideRdvElts(dayElt) {
        const eventElts = dayElt.querySelectorAll('[data-rdv-id]')

        const othersEventsElt = dayElt.querySelector('.calendar-others-events')
        if (othersEventsElt) {
            othersEventsElt.remove()
        }

        const maxHeight = (dayElt.clientHeight - 24) / 21.2

        dayElt.querySelectorAll('[data-rdv-id]').forEach(divElt => {
            divElt.classList.remove('d-none')
        })

        let sumHeightdivElts = 44
        dayElt.querySelectorAll('[data-rdv-id]').forEach(divElt => {
            const styles = window.getComputedStyle(divElt)
            sumHeightdivElts = sumHeightdivElts + divElt.clientHeight + parseFloat(styles['marginTop']) + parseFloat(styles['marginBottom'])
            if (sumHeightdivElts > dayElt.clientHeight && eventElts.length > maxHeight) {
                divElt.classList.add('d-none')
            }
        })

        if (sumHeightdivElts > dayElt.clientHeight && eventElts.length > maxHeight) {
            const divElt = document.createElement('div')
            divElt.className = 'calendar-others-events bg-primary text-light fw-bold'
            divElt.dataset.title = 'Voir tous les rendez-vous du jour'
            divElt.textContent = (parseInt(eventElts.length - maxHeight) + 2) + ' autres...'
            dayElt.insertBefore(divElt, dayElt.lastChild)
            divElt.addEventListener('click', () => {    
                dayElt.style.overflow = 'initial'
                dayElt.querySelectorAll('[data-rdv-id]').forEach(elt => elt.classList.remove('d-none'))
                divElt.remove()
            })
        }
    }

    /**
     * Edit the width of the calendar container (full or not).
     */
     #editWidthCalendar() {       
        localStorage.setItem('calendar.full_width', this.checkboxFullWidth.checked)

        if (this.checkboxFullWidth.checked) {
            this.sectionContainerElt.classList.replace('container', 'container-fluid')
        } else {
            this.sectionContainerElt.classList.replace('container-fluid', 'container')
        }
    }

    /**
     * Hide or show the weekend columns.
     */
    #hideWeekends() {
        this.weekendElts.forEach(elt => {
            elt.classList.toggle('d-none')
        })

        localStorage.setItem('calendar.show_weekends', this.checkboxShowWeekend.checked)
    }
}
