import AlertMessage from "./AlertMessage"
import Loader from "./loader"

/**
 * Redirect if the condition is true.
 */
export default class RedirectChecker {
    /**
     * @param {boolean} condition 
     * @param {string} url 
     * @param {string} alert 
     * @param {message} message 
     * @param {number} delay 
     */
    constructor(condition, url, alert, message, delay) {
        this.condition = condition
        this.url = url ?? location.pathname
        this.alert = alert ?? 'info'
        this.message = message
        this.delay = delay ?? 5 * 1000
        this.loader = new Loader()

        this.#check()
    }

     #check() {
        if (this.condition === true) {
            if (this.message) {
                new AlertMessage(this.alert, this.message, this.delay)
            }
            setTimeout(() => {
                this.loader.on()
                document.location.href = this.url
            }, this.delay)        
        }
    }
}