/**
 * Tool to format date.
 */
export default class DateFormatter {

    /**
     * Return the current date.
     * 
     * @param {string} separator 
     * 
     * @returns {string}
     */
    getDateNow(separator = '-') {
        const now = new Date()
        const month = now.getMonth() + 1

        return now.getFullYear() + separator + month.toString().padStart(2, '0') + separator + now.getDate().toString().padStart(2, '0')
    }

    /**
     * Return the current time.
     * 
     * @returns {string}
     */
    getTimeNow() {
        const now = new Date()

        return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0')
    }

    /**
     * Return the current hour.
     * 
     * @returns {string}
     */
    getHour() {
        const now = new Date()

        return now.getHours().toString().padStart(2, '0') + ':00'
    }

    /**
     * Return a locale formated date from a string date.
     * 
     * @param {Date | string} date
     * @param {string} type
     * @param {string} separator
     * @param {string} locale
     * 
     * @returns {string}
     */
    format(date, type = 'datetime', separator = '-', locale = 'fr') {
        if (date === null) {
            return ''
        }

        if (!(date instanceof Date)) {
            if (date.includes('T00:00:00')) {
                type = 'date'
            }

            date = new Date(date)
        }

        const month = date.getMonth() + 1

        switch (type) {
            case 'date':
                return date.toLocaleDateString(locale)
            case 'd/m':
                return date.toLocaleDateString(locale).substring(3, 10)
            case 'time':
                return date.toLocaleTimeString(locale).substring(0, 5)
            case 'dateInput':
                return date.getFullYear() + separator + month.toString().padStart(2, '0')
                    + separator + date.getDate().toString().padStart(2, '0')
            case 'datetimeInput':
                return date.getFullYear() + separator + month.toString().padStart(2, '0')
                    + separator + date.getDate().toString().padStart(2, '0')
                    + 'T' + date.toLocaleTimeString(locale).substring(0, 5)
            default:
                return date.toLocaleDateString(locale) + ' ' + date.toLocaleTimeString(locale).substring(0, 5)
        }
    }
}