/**
 * Tool to format date
 */
export default class DateFormat {

    /**
     * Return the current date.
     * @param {String} separator 
     * @returns {String}
     */
    getDateNow(separator = '-') {
        const now = new Date()
        const month = now.getMonth() + 1

        return now.getFullYear() + separator + month.toString().padStart(2, '0') + separator + now.getDate().toString().padStart(2, '0')
    }

    /**
     * Return the current time.
     * @returns {String}
     */
    getTimeNow() {
        const now = new Date()

        return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0')
    }

    /**
     * Return the current hour.
     * @returns {String}
     */
    getHour() {
        const now = new Date()

        return now.getHours().toString().padStart(2, '0') + ':00'
    }

    /**
     * Return a locale formated date from a string date.
     * @param {String} date 
     * @param {String} type 
     * @param {String} locale 
     * @returns {String}
     */
    getDate(date, type = 'datetime', locale = 'fr') {
        if (date === null) {
            return ''
        }
        
        date = new Date(date)

        switch (type) {
            case 'date':
                return date.toLocaleDateString(locale)
                break
            case 'd/m':
                return date.toLocaleDateString(locale).substring(3, 10)
                break
            case 'time':
                return date.toLocaleTimeString(locale).substring(0, 5)
                break
            default:
                return date.toLocaleDateString(locale) + ' ' + date.toLocaleTimeString(locale).substring(0, 5)
                break
        }
    }
}