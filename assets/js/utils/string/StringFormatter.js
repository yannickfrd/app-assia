
export default class StringFormatter {

    /**
     * Format amount to money string (€).
     * 
     * @param {number} value 
     * 
     * @returns {string}
     */
    formatAmount(value) {
        if (value || value === 0) {
            return value.toFixed(2).replace('.', ',') + '\xa0€'
        }

        return ''
    }

    /**
     * Round a amount.
     * 
     * @param {number} amount 
     */
     roundAmount(amount) {
        return amount ? Math.round(amount * 100) / 100 : ''
    }

    /**
     * Slice a text with a limit.
     * 
     * @param {string} text 
     * @param {number} limit 
     */
    slice(text, limit = 50) {
        if (!text || text.length < limit) {
            return text
        }

        return text.slice(0, limit) + ' [...]'
    }   
}