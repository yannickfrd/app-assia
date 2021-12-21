import MessageFlash from '../utils/messageFlash'
import Loader from '../utils/loader'
import Ajax from '../utils/ajax'
import Tag from './model/Tag'
import 'select2'

export default class TagService {
    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.tagsTemp = [] // Tableau de Tag
        this.countAddingTag = 0
        this.serviceId = parseInt(window.location.href.split('/').pop())
        this.colorTheme = document.getElementById('header').dataset.color
        this.listBadge = document.getElementById('tags-list')
        this.formTags = document.forms['service_tag']
        this.selectTags = document.getElementById('service_tag_tags')

        this.init()
    }

    init() {
        this.tagsIds = () => {
            const tagsIds = []
            this.listBadge.querySelectorAll('span[data-tag-id]').forEach(span => {
                tagsIds.push(span.dataset.tag)
            })

            return tagsIds
        }

        this.initTagMultiSelect()
        this.initTagsTemp()
        this.updateListOptions()

        this.formTags.addEventListener('submit', this.add.bind(this))

        const aElts = this.listBadge.querySelectorAll('span.badge a')

        if (aElts){
            aElts.forEach(aElt => {
                aElt.addEventListener('click', e => {
                    e.preventDefault()
                    this.tryDelete(aElt.href)
                })
            })
        }
    }
    
    initTagMultiSelect() {
        $('#collapse-tags').on('shown.bs.collapse', () => {
            $(`select[data-select2-id='tags']`).select2({
                placeholder: ' -- Étiquettes --',
                width: '100%',
                'language': {
                    'noResults': () => 'Aucun résultat.'
                },
            })
        })
    }

    /**
     * @param {Event} e 
     */
    add(e) {
        e.preventDefault()
        this.loader.on()

        const formData = new FormData(e.currentTarget)
        if (this.tagsIds().length > 0){
            this.tagsIds().forEach(id => {
                formData.append('service_tag[tags][]', id)
            })
        }

        if (formData.has('service_tag[tags][]')) { // S'il n'est pas vide
            formData.forEach(optionId => {
                const options = document.getElementById('service_tag_tags').options
                options.forEach(tag => {
                    if (optionId === tag.getAttribute('value')) {
                        this.countAddingTag++
                        this.tagsTemp.push(new Tag(optionId, tag.innerText, tag.getAttribute('data-select2-id')))
                    }
                })
            })
            this.ajax.send(e.currentTarget.method, e.currentTarget.action, this.responseAjax.bind(this), formData)

        } else {
            new MessageFlash('warning', 'Vous n\'avez pas sélectionné d\'étiquettes.')
            this.loader.off()
        }
    }

    /**
     * @param {String} href 
     */
    tryDelete(href) {
        this.loader.on()
        this.ajax.send('DELETE', href, this.responseAjax.bind(this))
    }

    /**
     * @param {Object} response 
     */
    responseAjax(response) {
        switch (response.action) {
            case 'add':
                this.addTag(response)
                break
            case 'delete':
                this.deleteTag(response)
                break
        }
        this.loader.off()
    }

    /**
     * @param {Object} response 
     */
    addTag(response) {
        if ('success' === response.alert) {
            for (let i = 1; i <= this.countAddingTag; i++) {
                this.createNodeTag(this.tagsTemp.at(-i))
            }
        } else {
            for (let i = 1; i <= this.countAddingTag; i++) {
                this.tagsTemp.splice(this.tagsTemp.at(-i))

                new MessageFlash(response.alert, response.msg)
            }
        }
        this.countAddingTag = 0
    }

    /**
     * @param {Object} response 
     */
    deleteTag(response) {
        if ('success' != response.alert) {
            return new MessageFlash(response.alert, response.msg)
        }

        const tag = document.querySelector(`#tags-list span[data-tag-id="${response.data.tagId}"]`)
        
        if (tag) {
            tag.remove()
        }  
    }

    initTagsTemp() {
        this.listBadge.querySelectorAll('span[data-tag-id]').forEach(tag => {
            this.tagsTemp.push(new Tag(tag.dataset.tag, tag.dataset.name))
        })
    }

    /**
     * Désactive les options de la liste des tags en fonction des tags déjà ajoutés
     */
    updateListOptions() {
        this.tagsTemp.forEach(tag => {
            this.formTags.querySelectorAll('select#service_tag_tags option').forEach(option => {
                if (parseInt(option.value) === parseInt(tag.id)) {
                    option.remove()
                }
            })
        })

        $(`select[data-select2-id='tags']`).val('').trigger('change')
    }

    /**
     * @param {Object} data 
     */
    createNodeTag(data) {
        if ('object' === typeof data) {
            this.addTagInHtml(data.name, data.id)
        } else {
            data.forEach(tag => this.createTag(tag))
        }
        this.updateListOptions()
    }

    /**
     * @param {String} tagText 
     * @param {Number} optionId 
     */
    addTagInHtml(tagText, optionId) {
        if (this.listBadge.querySelector('p')) {
            this.listBadge.querySelector('p').remove()
        }
        this.listBadge.appendChild(this.createTag({name: tagText, id: optionId}))
    }

    /**
     * @param {Object} tag 
     * @returns {HTMLSpanElement}
     */
    createTag(tag) {
        const tagSpanElt = document.createElement('span')
        tagSpanElt.dataset.tagId = tag.id
        tagSpanElt.dataset.tagName = tag.name
        tagSpanElt.setAttribute('data-tag-name', tag.name)
        tagSpanElt.classList.add('badge', 'bg-' + this.colorTheme, 'text-light', 'mr-1', 'tag')
        tagSpanElt.innerText = ' ' + tag.name + ' '

        // Bouton de suppression
        const aElt = document.createElement('a')
        aElt.href = `/service/${this.serviceId}/delete-tag/${tag.id}`
        aElt.onclick = e => {
            e.preventDefault()
            this.tryDelete(aElt.href)
        }
        const spanElt = document.createElement('span')
        spanElt.classList.add('badge', 'badge-danger', 'ml-2')
        spanElt.innerHTML = '<i class="fas fa-times"></i>'

        aElt.appendChild(spanElt)
        tagSpanElt.appendChild(aElt)

        return tagSpanElt
    }

    /**
     * @param {Object} tag 
     * @returns {HTMLOptionElement}
     */
    createOption(tag) {
        const optionElt = document.createElement('option')
        optionElt.value = tag.id
        optionElt.text = tag.name

        return optionElt
    }
}
