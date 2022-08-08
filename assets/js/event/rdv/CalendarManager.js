import AbstractManager from '../../AbstractManager'
import RdvForm from './RdvForm'
import ApiCalendar from '../../api/ApiCalendar'
import RdvModel from './model/RdvModel'
import AlertMessage from '../../utils/AlertMessage'

export default class CalendarManager extends AbstractManager {

    constructor() {
        super('rdv', '#container_events')

        this.form = new RdvForm(this)
        // this.apiCalendar = new ApiCalendar()

        this.fullWidthCheckbox = document.getElementById('full-width');
        this.showWeekendCheckbox = document.getElementById('show-weekend');
        this.weekendElts = document.querySelectorAll('div[data-weekend=true]')

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
            this.fullWidthCheckbox.checked = 'checked'
        }

        this.#editWidthCalendar()

        this.fullWidthCheckbox.addEventListener('click', () => this.#editWidthCalendar())

        if (this.showWeekendsItem === 'true') {
            this.showWeekendCheckbox.checked = 'checked'
        } else {
            this.#hideWeekends();
        }

        this.showWeekendCheckbox.addEventListener('click', () => this.#hideWeekends())
    }

    /**
     * Action after the ajax response.
     * 
     * @param {Object} response
     */
    responseAjax(response) {
        if (response.action) {
            const rdv = response.rdv
            const apiUrls = response.apiUrls

            this.checkActions(response, rdv)

            if (response.action === 'create') {
                
            }
        }

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        this.objectModal.hide()
    }

    extraUpdatesElt(rdv, rdvElt) {
        const dayElt = rdv.day ? document.getElementById(rdv.day) : document.getElementById(rdv.start.substr(0, 10))

        if (!dayElt) {
            return rdvElt.remove()
        }

        dayElt.insertBefore(rdvElt, dayElt.lastChild)
        this.#sortDayContainer(dayElt)
        this.#hideRdvElts(dayElt)
    }

    // /**
    //  * Add listeners to Rdv element (show).
    //  * 
    //  * @param {HTMLElement} eventElt 
    //  */
    //  #addListenersToObject(eventElt) {
    //     const id = eventElt.dataset.rdvId
    //     // Show Rdv
    //     eventElt.addEventListener('click', () => this.form.requestShow(id))
    // }

    // /**
    //  * Create the event element in the calendar container.
    //  * 
    //  * @param {Object} data
    //  */
    // #createRdvElt(data) {
    //     const rdv = data.rdv
    //     const apiUrls = data.apiUrls
    //     const eventElt = document.createElement('a')
    //     eventElt.className = `calendar-event bg-primary text-light`
    //     eventElt.dataset.rdvId = rdv.id
    //     eventElt.dataset.title = 'Voir le rendez-vous'

    //     eventElt.innerHTML = rdv.start.substr(11, 5) + ' ' + rdv.title
    //     const dayElt = rdv.day ? document.getElementById(rdv.day) : document.getElementById(rdv.start.substr(0, 10))

    //     if (dayElt) {
    //         dayElt.insertBefore(eventElt, dayElt.lastChild)
    //         this.#sortDayContainer(dayElt)
    //         this.#hideRdvElts(dayElt)
    //     }

    //     this.#addListenersToObject(eventElt)

    //     //v1
    //     // if (data.action === 'create') {
    //     //     this.apiCalendar.addEvent(new RdvModel(rdv), apiUrls)
    //     // }
    //     // v2 ...
    //     // this.apiCalendar.execute(action, apiUrls)

    //     this.form.closeModal()
    // }

    // /**
    //  * Update the event element in the calendar container.
    //  * 
    //  * @param {Object} data
    //  */
    // #updateRdvElt(data) {
    //     document.querySelector(`[data-rdv-id="${data.rdv.id}"]`).remove()
    //     this.#createRdvElt(data)
        
    //     this.form.updateApiRdv(data.rdv, data.apiUrls)
    // }

    // /**
    //  * Delete the event element in the calendar container.
    //  * 
    //  * @param {Object} rdv
    //  * @param {Object} apiUrls
    //  */
    // #deleteRdvElt(rdv, apiUrls) {
    //     document.querySelector(`[data-rdv-id="${rdv.id}"]`).remove()

    //     // this.apiCalendar.execute('delete', apiUrls)

    //     this.form.closeModal()
    // }

    /**
     * Sort the events in the day container.
     * 
     * @param {HTMLElement} dayElt
     */
    #sortDayContainer(dayElt) {
        const rdvArr = []
        dayElt.querySelectorAll('.calendar-event').forEach(eventElt => {
            rdvArr.push(eventElt)
        })

        rdvArr.sort((a, b) => a.innerText > b.innerText ? 1 : -1)
            .map(node => dayElt.appendChild(node))
    }

    /**
     * Hide the events if to many in the day container (height).
     * 
     * @param {HTMLElement} dayElt
     */
    #hideRdvElts(dayElt) {
        const eventElts = dayElt.querySelectorAll('.calendar-event')

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
            if (sumHeightdivElts > dayElt.clientHeight && eventElts.length > maxHeight) {
                divElt.classList.add('d-none')
            }
        })

        if (sumHeightdivElts > dayElt.clientHeight && eventElts.length > maxHeight) {
            const divElt = document.createElement('a')
            divElt.className = 'calendar-others-events bg-primary text-light fw-bold'
            divElt.href = '/calendar/day/' + dayElt.id.replaceAll('-', '/')
            divElt.dataset.title = 'Voir tous les rendez-vous du jour'
            divElt.textContent = (parseInt(eventElts.length - maxHeight) + 2) + ' autres...'
            dayElt.insertBefore(divElt, dayElt.lastChild)
        }
    }

    /**
     * Edit the width of the calendar container (full or not).
     */
     #editWidthCalendar() {
        if (this.fullWidthCheckbox.checked) {
            this.containerElt.classList.replace('container', 'container-fluid');
            localStorage.setItem('calendar.full_width', true);
        } else {
            this.containerElt.classList.replace('container-fluid', 'container');
            localStorage.setItem('calendar.full_width', false);
        }
    }

    /**
     * Hide or show the weekend columns.
     */
    #hideWeekends() {
        this.weekendElts.forEach(elt => {
            elt.classList.toggle('d-none');
        });
        if (this.showWeekendCheckbox.checked) {
            localStorage.setItem('calendar.show_weekends', true);
        } else {
            localStorage.setItem('calendar.show_weekends', false);
        }
    }
}
