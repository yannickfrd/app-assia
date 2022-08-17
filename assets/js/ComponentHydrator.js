import TagsManager from './tag/TagsManager'
import StringFormatter from './utils/string/StringFormatter'
import DateFormatter from './utils/date/DateFormatter'

/**
 * Hydrate a HTML component (all 'data-object-key') with the object data.
 */
export default class ComponentHydrator {

    constructor() {
        this.tagsManager = new TagsManager()
        this.stringFormatter = new StringFormatter()
        this.dateFormatter = new DateFormatter()
    }

    /**
     * Hydrate a component element in the DOM.
     *
     * @param {Object} object
     * @param {HTMLElement} objectElt
     * 
     */
     hydrate(object, objectElt) {
        objectElt.querySelectorAll('[data-object-key]').forEach(elt => {
            const key = elt.dataset.objectKey
            const value = object[key + 'ToString'] ?? object[key]
    
            if (key.endsWith('Amt') && typeof value === 'number') {
                return elt.textContent = this.stringFormatter.formatAmount(value)
            }
           
            if (typeof value === 'string' && value.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})\+(\d{2}):(\d{2})$/)) {
                return elt.textContent = this.dateFormatter.format(value)
            }

            if (elt.type === 'checkbox') {
                return elt.checked = value
            }

            if (key === 'tags' && value.length > 0) {
                return this.tagsManager.updateTagsContainer(elt, value)
            }

            if (key === 'alerts' && value.length > 0) {
                return elt.innerHTML = this.createAlerts(value)
            } 
    
            if (key === 'supportGroup' && value) {
                return elt.textContent = value.header.fullname
            }

            if (key === 'service' && object.supportGroup) {
                return elt.textContent = object.supportGroup.service.name
            }

            if ((key === 'createdBy' || key === 'updatedBy') && value instanceof Object && value.fullname) {
                return elt.textContent = value.fullname
            }

            if (typeof value === 'number') {
                return elt.textContent = parseInt(value).toLocaleString('fr')
            }

            if (value !== undefined  && (value instanceof Object) === false) {
                elt.innerHTML =  this.stringFormatter.slice(value, 100)
            }
        })
     }

    /**
     * Create alert elements.
     * 
     * @param {Object[]} alerts
     * @returns {string}
     */
     createAlerts(alerts) {
        let alertElts = []

        if (alerts.length > 0) {
            alertElts.push(`<span title="${alerts.length} rappel(s)">${alerts[0].dateToString}
                <i class="fas fa-bell text-secondary"></i></span>`)
        }

        return alertElts.join('')
    }
}