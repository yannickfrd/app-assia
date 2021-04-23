import FieldDisplayer from '../utils/form/fieldDisplayer'

export default class UpdateDevice {

    constructor() {
        this.placeSelect = document.getElementById('device_place')
        this.contributionSelect = document.getElementById('device_contribution')
        this.contributionTypeSelect = document.getElementById('device_contribution_type')
        this.contributionRateSelect = document.getElementById('device_contribution_rate')
        this.prefix = 'device_'
        this.init()
    }

    init() {
        new FieldDisplayer(this.prefix, 'place', [1])
        new FieldDisplayer(this.prefix, 'contribution', [1])
        new FieldDisplayer(this.prefix, 'contributionType', [1, 3])
    }
}