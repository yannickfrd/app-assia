
export default class TagView {
    constructor(themeColor = null) {
        this.themeColor = themeColor
    }

    /**
     * @param {*} tags 
     * @returns {Array}
     */
    collectionTagsToArray(tags){
        const arTags = []

        if (Array.isArray(tags)){
            for (let i = 0; i < tags.length; i++) {
                arTags.push(tags[i])
            }
            return arTags;
        }

        for (const [key, tag] of Object.entries(tags)) {
            arTags.push(tag)
        }

        return arTags
    }

    /**
     * @param {*} tags 
     * @returns {Array}
     */
    collectionTagsView(tags){
        const dataTags = this.collectionTagsToArray(tags)
        const arTags = []

        for (let i = 0; i < dataTags.length; i++) {
            arTags.push(this.createTagElt(dataTags[i]))
        }

        return arTags
    }

    /**
     * @param {Object} tag 
     * @returns {HTMLSpanElement}
     */
    createTagElt(tag){
        const spanElt = document.createElement('span')
        spanElt.setAttribute('data-tag', tag.id)
        spanElt.setAttribute('data-name', tag.name)
        spanElt.classList.add('tag', 'badge', 'bg-'+ this.themeColor, 'text-light', 'mr-1')
        spanElt.innerText = ' ' + tag.name + ' '

        return spanElt
    }
}