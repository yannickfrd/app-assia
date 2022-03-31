import 'select2'

/**
 * Gestion des selects (actuellement via la librairie "select2").
 * https://select2.org/
 */
export default class SelectManager {
    /**
     * @param {string} selector
     * @param {{name: string, elementId: string}} event
     * @param {Object} options
     */
    constructor(selector, event = {}, options = {}) {
        this.options = options
        this.event = event
        this.selectElt = document.querySelector(selector)

        if (this.selectElt === null) {
            return
        }
        this.select2 = $(selector)
        this.listSelectOptions = this.selectElt.options

        this.init()
    }

    init() {
        this.initOptions()

        this.switchEvent()
    }

    initOptions() {
        // Permet de savoir si un objet est vide ou pas.
        const isObjEmpty = (obj) => Object.keys(obj).length === 0
        const defaultOptions = this.getDefaultOptions()
        // Init les options du select2 si pas d'options en paramètre.
        if (isObjEmpty(this.options)) {
            this.options = defaultOptions
        } else {
            for (let key in this.options) {
                defaultOptions[key] = this.options[key]
            }
        }
        this.options = defaultOptions;
    }
    /**
     * Retourne les options par défaut du select2.
     * @returns {Object}
     */
    getDefaultOptions() {
        if (!this.selectElt.getAttribute('multiple')) {
            return {
                width: '100%',
                theme: 'bootstrap4',
                language: {
                    'noResults': () => 'Aucun résultat.',
                },
            }
        }

        return {
            placeholder: ' ' + (this.selectElt.getAttribute('placeholder') ?? '-- Sélectionner --'),
            allowClear: true,
            width: 'resolve',
            'language': {
                'noResults': () => '<span class="text-secondary">Aucun résultat.</span>',
                'removeAllItems': () => 'Tout effacer',
            },
            escapeMarkup: (markup) => {
                return markup
            }
        }
    }

    /**
     * Efface toutes les sélections.
     */
    clearSelect() {
        this.select2.val(null).trigger('change')
    }

    /**
     * Met à jour la sélection.
     */
    updateSelect(values) {
        this.clearSelect()
        this.select2.val(values).trigger('change')
    }

    /**
     * Supprime les options du select en fonction d'un tableau contenant des ids.
     * @param {[{id: number}]} elts
     */
    clearOptionsList(elts) {
        elts.forEach(element => {
            Array.from(this.listSelectOptions).forEach(option => {
                if (parseInt(option.value) === parseInt(element.id)) {
                    option.remove()
                }
            })
        })
    }

    /**
     * Crée et ajoute une option au select.
     *
     * @param {string|number} value
     * @param {string} text
     */
    addOption(value, text) {
        const optionElt = this.createOption(value, text)

        this.selectElt.add(optionElt, null)
    }

    /**
     * Permet de créer une option pour le select.
     *
     * @param {string|number} value
     * @param {string} text
     *
     * @returns {HTMLOptionElement}
     */
    createOption(value, text) {
        const option = document.createElement('option')
        option.value = value.toString()
        option.text = text

        return option
    }

    /**
     * Affiche dans le select en fonction d'un liste d'ids.
     *
     * @param {Array} arrayList
     */
    showOptionsFromArray(arrayList) {
        if (arrayList.length !== 0) {
            return this.select2.val(arrayList).trigger('change')
        }

        return this.clearSelect()
    }

    /**
     * Permet de gérer les différents évènements demandés.
     */
    switchEvent() {
        switch (this.event.name) {
            case 'onCollapse':
                this.onCollapseEvent()
                break
            case 'onModal':
                this.onModalEvent()
                break
            default:
                this.defaultEvent()
                break
        }
    }

    /**
     * Event: "on collapse".
     * */
    onCollapseEvent() {
        if (!this.event.elementId) {
            console.error(
                'L\'id du collapse est maquant. Veuillez le renseigner dans les options. ' +
                '"collapseId" doit être renseigné dans les options si l\'event "onCollapse" est appelé.'
            )
        }

        $('#' + this.event.elementId).on('shown.bs.collapse', () => this.defaultEvent())
    }

    /**
     * Event: "on modal" .
     */
    onModalEvent() {
        if (!this.event.elementId) {
            console.error(
                'L\'id du modal est maquant. Veuillez le renseigner dans les options. ' +
                '"modalId" doit être renseigné dans les options si l\'event "onCollapse" est appelé."'
            )
        }

        this.clearSelect()

        $('#' + this.event.elementId).on('shown.bs.modal', () => this.defaultEvent())
    }

    /**
     * Event: initialisation par défaut.
     */
    defaultEvent() {
        this.select2.select2(this.options)
    }

    checkSelect2Style() {
        if (this.selectElt.nextElementSibling === null) return;
        this.selectElt.nextElementSibling.querySelectorAll('.select2-search__field').forEach(select2Elt => {
            select2Elt.style.width = '100%'
        })
    }
}