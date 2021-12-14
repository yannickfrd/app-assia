import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import DateFormater from '../utils/date/dateFormater'
import { Modal } from 'bootstrap'
import ParametersUrl from '../utils/parametersUrl'
import Google from "../api/googleApi/google";

export default class Calendar {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        this.parametersUrl = new ParametersUrl()
        this.modalElt = new Modal(document.getElementById('modal-rdv'))

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

        this.themeColor = document.getElementById('header').dataset.color
        this.supportElt = document.getElementById('support')
        this.supportPeopleElt = document.getElementById('js-support-people')

        this.showWeekendsItem = localStorage.getItem('agenda.show_weekends')
        this.fullWidthItem = localStorage.getItem('agenda.full_width')

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
    }

    /**
     * Modifie la largeur de l'agenda.
     */
    changeWidthCalendar() {
        if (this.fullWidthCheckbox.checked) {
            this.calendarContainer.classList.replace('container', 'container-fluid');
            localStorage.setItem('agenda.full_width', true);
        } else {   
            this.calendarContainer.classList.replace('container-fluid', 'container');
            localStorage.setItem('agenda.full_width', false);
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
            localStorage.setItem('agenda.show_weekends', true);
        } else {
            localStorage.setItem('agenda.show_weekends', false);
        }
    }

    /**
     * Réinialise le formulaire modal de rdv.
     * @param {Event} e 
     */
    resetData(e) {
        e.preventDefault()
        if (this.supportElt) {
            this.modalRdvElt.querySelector('form').action = '/support/' + this.supportElt.dataset.support + '/rdv/new'
            const fullname = this.supportPeopleElt.querySelector('.btn').textContent
            this.modalRdvElt.querySelector('#rdv_title').value = fullname
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

        this.modalRdvElt.querySelector('#rdv_content').value = ''
        this.btnDeleteElt.classList.add('d-none')
        this.btnSaveElt.classList.remove('d-none')

        if (e.target.className && e.target.className.search('calendar-event') != 0) {
            this.modalElt.show()
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
        this.ajax.send('GET', '/rdv/' + this.rdvId + '/get', this.responseAjax.bind(this))
    }

    /**
     * Requête pour sauvegarder le RDV.
     * @param {Event} e 
     */
    requestSaveRdv(e) {
        e.preventDefault()

        // if (this.modalRdvElt.querySelector('#rdv_title').value === '') {
        //     return new MessageFlash('danger', 'La rdv est vide.')
        // }

        if (!this.loader.isActive()) {
            this.updateDatetimes()

            if (this.formRdvElt.elements['rdv_googleCalendar'].checked) {
                new Google()
            }
            // if (this.formRdvElt.elements)

            // this.loader.on()
            // this.ajax.send('POST', this.formRdvElt.getAttribute('action'), this.responseAjax.bind(this), new FormData(this.formRdvElt))
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
                    this.createRdv(data.rdv);
                    break;
                case 'update':
                    this.updateRdv(data.rdv);
                    break;
                case 'delete':
                    this.deleteRdv(data.rdv);
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
            const aElt = `<a href="${href}" class="text-${this.themeColor}" title="Accéder au suivi">${title}</a>`
            this.rdvTitleElt.innerHTML = aElt
        }

        if (rdv.canEdit) {
            this.btnDeleteElt.href = `/rdv/${this.rdvId}/delete` 
            this.btnDeleteElt.classList.remove('d-none')
            this.btnSaveElt.classList.remove('d-none')
        } else {
            this.btnDeleteElt.classList.add('d-none')
            this.btnSaveElt.classList.add('d-none')
        }
        
        this.modalElt.show()
    }

    /**  
     * Donnes les informations sur l'enregistrement (date de création, créateur...).
     * @param {Object} rdv
     */
    getInfoRdvElt(rdv) {
        let htmlContent = `Créé le ${rdv.createdAt} par ${rdv.createdBy}`
        if (rdv.createdAt != rdv.updatedAt) {
            htmlContent = htmlContent + `<br/> (modifié le ${rdv.updatedAt} par ${rdv.updatedBy})`
        }
        return htmlContent
    }

    /**
     * Crée le RDV dans le container du jour de l'agenda.
     * @param {Object} rdv 
     */
    createRdv(rdv) {
        const rdvElt = document.createElement('div')
        rdvElt.className = `calendar-event bg-${this.themeColor} text-light`
        rdvElt.id = `rdv-${rdv.id}`
        rdvElt.dataset.title = 'Voir le rendez-vous'

        const title = this.modalRdvElt.querySelector('#rdv_title').value

        rdvElt.innerHTML = rdv.start + ' ' + title
        const dayElt = document.getElementById(rdv.day)

        if (dayElt) {
            dayElt.insertBefore(rdvElt, dayElt.lastChild)
            this.sortDayBlock(dayElt)
            this.hideRdvElts(dayElt)
        }

        rdvElt.addEventListener('click', this.requestGetRdv.bind(this, rdvElt))
    }

    /**
     * Met à jour le RDV dans l'agenda.
     * @param {Object} rdv 
     */
    updateRdv(rdv) {
        this.rdvElt.remove()
        this.createRdv(rdv)
    }

    /**
     * Supprime le RDV dans l'agenda.
     */
    deleteRdv() {
        const rdvElt = document.getElementById('rdv-' + this.rdvId)
        const dayElt = rdvElt.parentNode
        rdvElt.remove()
        this.hideRdvElts(dayElt)
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