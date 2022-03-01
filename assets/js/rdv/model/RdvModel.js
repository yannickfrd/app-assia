
export default class RdvModel {
    _baseUrlGoogle = 'https://calendar.google.com/calendar/u/0/r/eventedit?'
    _baseUrlOutlook = 'https://outlook.live.com/calendar/0/deeplink/compose?'

    _apiName;
    _title;
    _start;
    _end;
    _location;
    _content;

    constructor(apiName, rdvEntity) {
        this._apiName = apiName;

        this._title = rdvEntity.title;
        this._start = rdvEntity.start
        this._end = rdvEntity.end
        this._location = rdvEntity.location
        this._content = rdvEntity.content
    }

    get url() {
        const apiNameIsGoogle = this.apiName === 'google'

        let url = this.apiName === 'google' ? this._baseUrlGoogle : this._baseUrlOutlook
        url += apiNameIsGoogle
            ? 'text=' + encodeURI(this.title) + '&ctz=Europe/Paris'
            : 'subject=' + encodeURI(this.title)

        if (this.content !== null) {
            url += apiNameIsGoogle ? '&details=': '&body='
            url += apiNameIsGoogle
                ? encodeURIComponent(this._createBodyEvent())
                : encodeURIComponent(this._createBodyEvent()).replaceAll('%0A', '%3Cbr%3E')
        }
        if (this.location !== null) {
            url += '&location=' + encodeURI(this.location)
        }
        if (this.start !== '' && this.end !== '') {
            if (apiNameIsGoogle) {
                url += '&dates='
                    + this.start.replaceAll('-', '').replace(':', '')
                    + '/' + this.end.replaceAll('-', '').replace(':', '');
            } else {
                url += '&startdt=' + this.start + '&enddt=' + this.end
            }
        }

        return url;
    }

    _createBodyEvent() {
        let content = '<p>' + this.content + '</p>'

        if (this.status !== '' && this.status !== undefined) {
            content += '<br><strong>Statut : </strong>' + this.status
        }

        return content
    }

    get start() {
        return this._start;
    }

    set start(value) {
        this._start = value;
    }

    get end() {
        return this._end;
    }

    set end(value) {
        this._end = value;
    }

    get location() {
        return this._location;
    }

    set location(value) {
        this._location = value;
    }

    get content() {
        return this._content;
    }

    set content(value) {
        this._content = value;
    }

    get apiName() {
        return this._apiName;
    }

    set apiName(value) {
        this._apiName = value;
    }

    get title() {
        return this._title;
    }

    set title(value) {
        this._title = value;
    }
}