import DisplayFields from '../utils/displayFields'

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
        new DisplayFields(this.prefix, 'place', [1])
        new DisplayFields(this.prefix, 'contribution', [1])
        new DisplayFields(this.prefix, 'contributionType', [1, 3])
    }
}