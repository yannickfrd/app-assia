import DisplayInputs from "../utils/displayInputs";

// Situation professionnelle
export default class sitProf {

    constructor() {
        this.cardSitProfElt = document.getElementById("accordion-sit-prof");
        this.bthElts = this.cardSitProfElt.querySelectorAll("button");
        this.init();

    }
    init() {
        let i = 0;
        this.bthElts.forEach(btnElt => {
            btnElt.addEventListener("click", this.editBtn.bind(this, btnElt));
            new DisplayInputs("support_grp_supportPers_", i + "_sitProf_profStatus", "select", [2, 3]);
            i++;
        });
    }

    editBtn(btnElt) {
        let active = false;
        if (btnElt.classList.contains("active")) {
            active = true;
        }
        this.bthElts.forEach(btn => {
            btn.classList.remove("active");
        });
        if (!active) {
            btnElt.classList.add("active");
        }
    }
}