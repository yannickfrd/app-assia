import AlertMessage from '../utils/AlertMessage'
import Loader from '../utils/loader'
import Ajax from '../utils/ajax'
import Tag from './model/Tag'
import SelectManager from '../utils/form/SelectManager'

export default class TagService {
    constructor() {
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)

        this.tagsTemp = [] // Tableau de Tag
        this.countAddingTag = 0
        this.serviceId = parseInt(window.location.href.split('/').pop())
        this.listBadge = document.getElementById('tags-list')
        this.formTags = document.forms['service_tag']
        this.selectTags = document.getElementById('service_tag_tags')
        this.selectManager = new SelectManager('#service_tag_tags')

        this.init()
    }

    init() {
        if (!this.formTags) {
            return console.error('No form tags')
        }

        this.tagsIds = () => {
            const tagsIds = []
            this.listBadge.querySelectorAll('span[data-tag-id]').forEach(span => {
                tagsIds.push(span.dataset.tagId)
            })

            return tagsIds
        }

        this.initTagsTemp()
        this.selectManager.clearOptionsList(this.tagsTemp)

        this.formTags.addEventListener('submit', this.add.bind(this))

        const aElts = this.listBadge.querySelectorAll('span.badge a')

        if (aElts) {
            aElts.forEach(aElt => {
                aElt.addEventListener('click', e => {
                    e.preventDefault()
                    this.tryDelete(aElt.href)
                })
            })
        }
    }

    /**
     * @param {Event} e
     */
    add(e) {
        e.preventDefault()
        this.loader.on()

        const formData = new FormData(e.currentTarget)
        if (this.tagsIds().length > 0) {
            this.tagsIds().forEach(id => {
                formData.append('service_tag[tags][]', id)
            })
        }

        if (formData.has('service_tag[tags][]')) { // S'il n'est pas vide
            formData.getAll('service_tag[tags][]').forEach(optionId => {
                const options = document.getElementById('service_tag_tags').options

                Array.from(options).forEach(tag => {
                    if (parseInt(optionId) === parseInt(tag.getAttribute('value'))) {
                        this.countAddingTag++
                        this.tagsTemp.push(new Tag(optionId, tag.innerText, tag.dataset.multiSelect))
                    }
                })
            })
            this.ajax.send(e.currentTarget.method, e.currentTarget.action, this.responseAjax.bind(this), formData)

        } else {
            new AlertMessage('warning', 'Vous n\'avez pas sélectionné d\'étiquettes.')
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

                new AlertMessage(response.alert, response.msg)
            }
        }
        this.countAddingTag = 0
    }

    /**
     * @param {Object} response
     */
    deleteTag(response) {
        if ('success' !== response.alert) {
            return new AlertMessage(response.alert, response.msg)
        }

        const tagElt = document.querySelector(`#tags-list span[data-tag-id="${response.data.tagId}"]`)

        if (tagElt) {
            this.selectManager.addOption(tagElt.dataset.tagId, tagElt.dataset.tagName)
            tagElt.remove()
        }
    }

    initTagsTemp() {
        this.tagsTemp = []
        this.listBadge.querySelectorAll('span[data-tag-id]').forEach(tagElt => {
            this.tagsTemp.push(new Tag(tagElt.dataset.tagId, tagElt.dataset.tagName))
        })
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
        this.selectManager.clearOptionsList(this.tagsTemp)
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
        tagSpanElt.classList.add('badge', 'bg-primary', 'me-1', 'tag')
        tagSpanElt.dataset.tagId = tag.id
        tagSpanElt.dataset.tagName = tag.name
        tagSpanElt.innerText = ' ' + tag.name + ' '

        // Bouton de suppression
        const aElt = document.createElement('a')
        aElt.href = `/service/${this.serviceId}/delete-tag/${tag.id}`
        aElt.onclick = e => {
            e.preventDefault()
            this.tryDelete(aElt.href)
        }
        const spanElt = document.createElement('span')
        spanElt.classList.add('badge', 'bg-danger', 'ms-2')
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
