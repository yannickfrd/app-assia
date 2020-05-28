export default class ParametersUrl {

    constructor() {
        var vars = {};
        window.location.href.replace(location.hash, "").replace(
            /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
            function (m, key, value) { // callback
                vars[key] = value !== undefined ? value : "";
            }
        );
        this.vars = vars;
    }

    getAll() {
        return this.vars;
    }

    get(param) {
        return this.vars[param] ? this.vars[param] : null;
    }
}