import FieldDisplayer from '../utils/form/fieldDisplayer'

export default class UpdateService {

    constructor() {
        this.placeSelect = document.getElementById('service_place')
        this.contributionSelect = document.getElementById('service_contribution')
        this.contributionTypeSelect = document.getElementById('service_contribution_type')
        this.contributionRateSelect = document.getElementById('service_contribution_rate')
        this.prefix = 'service_'
        this.init()
    }

    init() {
        new FieldDisplayer(this.prefix, 'place', [1])
        new FieldDisplayer(this.prefix, 'contribution', [1])
        new FieldDisplayer(this.prefix, 'contributionType', [1, 3])
    }
}