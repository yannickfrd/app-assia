export default class TagsManager {

    /**
     * @param {HTMLElement} containerElt
     * @param {Array|Object} tags
     */
    updateTagsContainer(containerElt, tags) {
        containerElt.textContent = ''

        if (Array.isArray(tags)) {
            tags.forEach(tag => {
                containerElt.appendChild(this.createTagElt(tag))
            })
            return
        }

        for (const [key, tag] of Object(tags)) {
            containerElt.appendChild(this.createTagElt(tag))
        }
    }

    /**
     * @param {NodeList} tagElts
     * @param {NodeList} tagOptionElts
     * @returns {Array}
     */
    getTagIds(tagElts, tagOptionElts) {
        const ids = []

        tagElts.forEach(tagElt => {
            tagOptionElts.forEach(option => {
                if (option.value === tagElt.dataset.tagId) {
                    ids.push(option.value)
                }
            })
        })

        return ids
    }

    /**
     * @param {Object} tag
     * @returns {HTMLSpanElement}
     */
    createTagElt(tag) {
        tag.color = tag.color ?? 'secondary'

        const spanElt = document.createElement('span')
        spanElt.dataset.tagId = tag.id
        spanElt.classList.add('badge', 'bg-' + tag.color, 'me-1')
        spanElt.textContent = tag.name

        return spanElt
    }
}