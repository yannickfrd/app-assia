import Loader from '../utils/loader'
import Ajax from '../utils/ajax'
import AlertMessage from "../utils/AlertMessage";
import WidgetCollectionManager from '../utils/form/WidgetCollectionManager'

export default class ServiceUserManager {

    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.btnAddServiceElt = document.querySelector('button#addService')
        this.tableElt = document.querySelector('#user_services_table')
        
        this.servicesCollectionManager = new WidgetCollectionManager(this.tableElt ? this.updateServicesSelect.bind(this) : null)

        this.init()
    }

    init() {
        if (!this.tableElt) {
            if (document.querySelector('#service_user_fields_list').children.length === 0) {
                this.servicesCollectionManager.addElt(document.querySelector('button[data-add-widget]'))
            }
            return
        }
        
        // To clicking on one of the checkboxes
        this.tableElt.querySelectorAll('input[data-action="toggle_main"]').forEach(checkbox => {
            checkbox.addEventListener('change', e => this.toggleMainService(e.currentTarget))
        })

        // To the click on the deletion of one of the services
        this.tableElt.querySelectorAll('button[data-action="delete"]').forEach(btnElt => {
            btnElt.addEventListener('click', e => this.requestToRemoveService(e))
        })
    }

    updateServicesSelect() {
        const servicesList = this.getServicesList()

        document.querySelectorAll('#service_user_fields_list option').forEach(optionElt => {
            if (servicesList[optionElt.value]) {
                optionElt.remove()
            }
        })
    }

    /**
     * @param {Event} e
     */
    requestToRemoveService(e) {
        e.preventDefault()

        if (window.confirm('Attention, vous allez retirer l\'utilisateur du service. \nConfirmer ?')) {
            this.loader.on()

            this.ajax.send('DELETE', e.currentTarget.dataset.path, this.responseAjax.bind(this))
        }
    }

    /**
     * @param {HTMLInputElement} checkboxElt
     */
    toggleMainService(checkboxElt) {
        this.loader.on()

        this.ajax.send('GET', checkboxElt.dataset.path, this.responseAjax.bind(this))
    }

    /** @param {Object} response */
    responseAjax(response) {
        switch (response.action) {
            case 'delete':
                this.deleteTr(response.service.id)
                this.addOption(response.service.id)
                break
        }

        if (response.msg) {
            new AlertMessage(response.alert, response.msg)
        }

        this.loader.off()
    }

    /**
     * @param {number} serviceId
     */
    deleteTr(serviceId) {
        const trElt = this.tableElt.querySelector(`tr[data-service-id="${serviceId}"]`)
        trElt.classList.add('fade-out')
        setTimeout(() => trElt.remove(), 200)
    }

    /**
     * Add the service in the options of select elements.
     * @param {number} serviceId
     */
    addOption(serviceId) {
        const servicesList = this.getServicesList()

        document.querySelectorAll('#service_user_fields_list select').forEach(selectElt => {
            const optionElt = document.createElement('option')
            optionElt.value = serviceId
            optionElt.textContent = servicesList[serviceId]

            selectElt.add(optionElt)

            this.btnAddServiceElt.addEventListener('click', () => selectElt.add(optionElt))
        })
    }

    /**
     * Get the list of services in the table.
     * @returns {Array}
     */
    getServicesList() {
        const servicesList = [];

        this.tableElt.querySelectorAll('tr[data-service-id]').forEach(trElt => {
            servicesList[trElt.dataset.serviceId] = trElt.dataset.serviceName
        })

        return servicesList;
    }
}