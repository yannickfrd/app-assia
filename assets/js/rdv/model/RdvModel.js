
export default class RdvModel {
    #baseUrlGoogle = 'https://calendar.google.com/calendar/u/0/r/eventedit?'
    #baseUrlOutlook = 'https://outlook.live.com/calendar/0/deeplink/compose?'

    #apiName
    #title
    #start
    #end
    #location
    #content
    #createdBy
    #supportGroupName

    /**
     * @param {String} apiName
     * @param {Object} rdvEntity
     */
    constructor(apiName, rdvEntity) {
        this.#apiName = apiName

        this.#title = rdvEntity.title
        this.#start = rdvEntity.start
        this.#end = rdvEntity.end
        this.#location = rdvEntity.location
        this.#content = rdvEntity.content
        this.#createdBy = rdvEntity.createdBy.fullname
        this.#supportGroupName = rdvEntity.supportGroup.header.fullname
    }

    get url() {
        const apiNameIsGoogle = this.apiName === 'google'

        let url = this.apiName === 'google' ? this.#baseUrlGoogle : this.#baseUrlOutlook
        url += apiNameIsGoogle
            ? 'text=' + encodeURI(this.title) + '&ctz=Europe/Paris'
            : 'subject=' + encodeURI(this.title)

        url += apiNameIsGoogle ? '&details=': '&body='
        url += apiNameIsGoogle
            ? encodeURIComponent(this.#createBodyEvent())
            : encodeURIComponent(this.#createBodyEvent()).replaceAll('%0A', '%3Cbr%3E')

        if (this.location !== null) {
            url += '&location=' + encodeURI(this.location)
        }
        if (this.start !== '' && this.end !== '') {
            if (apiNameIsGoogle) {
                url += '&dates='
                    + this.start.replaceAll('-', '').replace(':', '')
                    + '/' + this.end.replaceAll('-', '').replace(':', '')
            } else {
                url += '&startdt=' + this.start + '&enddt=' + this.end
            }
        }

        return url
    }

    #createBodyEvent() {
        let body = this.content !== null ? '<p>' + this.content + '</p>' : ''

        if (this.createdBy !== null && this.createdBy !== undefined) {
            body += '<br><strong>Créé par : </strong>' + this.createdBy
        }

        if (this.supportGroupName !== null && this.supportGroupName !== undefined) {
            body += '<br><strong>Nom du suivi : </strong>' + this.supportGroupName
        }

        return body
    }


    get supportGroupName() {
        return this.#supportGroupName;
    }

    set supportGroupName(value) {
        this.#supportGroupName = value;
    }

    get createdBy() {
        return this.#createdBy
    }

    set createdBy(value) {
        this.#createdBy = value
    }

    get start() {
        return this.#start
    }

    set start(value) {
        this.#start = value
    }

    get end() {
        return this.#end
    }

    set end(value) {
        this.#end = value
    }

    get location() {
        return this.#location
    }

    set location(value) {
        this.#location = value
    }

    get content() {
        return this.#content
    }

    set content(value) {
        this.#content = value
    }

    get apiName() {
        return this.#apiName
    }

    set apiName(value) {
        this.#apiName = value
    }

    get title() {
        return this.#title
    }

    set title(value) {
        this.#title = value
    }
}