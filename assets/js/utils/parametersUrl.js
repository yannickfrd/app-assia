/**
 * Donne les paramètres présents dans l'URL.
 */
export default class ParametersUrl {

    constructor() {
        const vars = {}
        window.location.href.replace(location.hash, '').replace(
            /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
            (m, key, value) => { // callback
                vars[key] = value !== undefined ? value : ''
            }
        )
        this.vars = vars
    }

    /**
     * Donne tous les paramètres.
     */
    getAll() {
        return this.vars
    }

    /**
     * Donne un paramètre.
     * @param {String} param 
     */
    get(param) {
        return this.vars[param] ? this.vars[param] : null
    }

    /**
     * Donne le dernier paramètre.
     */
    last() {
        const splitPath = window.location.pathname.split('/')
        return splitPath[splitPath.length - 1]
    }
}