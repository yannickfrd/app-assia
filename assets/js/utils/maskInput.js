import rxmask from 'rxmask';

export default class maskInput {

    constructor(type) {
        this.type = type;
        this.options = null;
        this.init();
    }

    init() {
        switch (this.type) {
            case '.js-phone':
                this.options = this.getPhoneOptions();
                break;
            case '.js-zipcode':
                this.options = this.getZipcodeOptions();
                break;
            case '.js-date':
                this.options = this.getDateOptions();
                break;
            case '.js-dept-code':
                this.option = this.getDeptOptions();
                break;
        }

        document.querySelectorAll(this.type).forEach(inputElt => {
            const parser = new rxmask(this.options, inputElt);
            inputElt.oninput = () => parser.onInput();
        })
    }

    getPhoneOptions() {
        return {
            mask: '__ __ __ __ __',
            placeholderSymbol: '_',
            allowedCharacters: '[0-9]',
            maxMaskLength: 14,
        }
    }

    getZipcodeOptions() {
        return {
            mask: '__ ___',
            placeholderSymbol: '_',
            allowedCharacters: '[0-9]',
            maxMaskLength: 6,
        }
    }

    getDateOptions() {
        return {
            mask: '__/__/____',
            placeholderSymbol: '_',
            allowedCharacters: '[0-9]',
            maxMaskLength: 10,
        }
    }

    getDeptOptions() {
        return {
            mask: '__',
            placeholderSymbol: '_',
            allowedCharacters: '[0-9]',
            maxMaskLength: 2,
        }
    }
}