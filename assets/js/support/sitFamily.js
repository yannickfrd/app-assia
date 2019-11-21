import DisplayInputs from "../utils/displayInputs";

// Situation familiale
export default class sitFamily {

    constructor() {
        this.prefix = "support_grp_sitFamilyGrp_";
        this.init();
    }
    init() {
        new DisplayInputs(this.prefix, "unbornChild", "select", [1]);
        new DisplayInputs(this.prefix, "famlReunification", "select", [1, 3, 4, 5]);
    }
}