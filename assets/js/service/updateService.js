import DisplayInputs from "../utils/displayInputs";

export default class UpdateService {

    constructor() {
        this.accommodationSelect = document.getElementById("service_accommodation");
        this.contributionSelect = document.getElementById("service_contribution");
        this.contributionTypeSelect = document.getElementById("service_contribution_type");
        this.contributionRateSelect = document.getElementById("service_contribution_rate");
        this.prefix = "service_";
        this.init();
    }

    init() {
        new DisplayInputs(this.prefix, "accommodation", "select", [1]);
        new DisplayInputs(this.prefix, "contribution", "select", [1]);
        new DisplayInputs(this.prefix, "contributionType", "select", [1, 3]);
    }
}