import AjaxRequest from "../utils/ajaxRequest";
import MessageFlash from "../utils/messageFlash";
import DisplayInputs from "../utils/displayInputs";
import ValidationInput from "../utils/validationInput";
import Select from "../utils/select";
import CheckDate from "../utils/checkDate";
import Loader from "../utils/loader";

// Contrôle saisie AVDL :
// - Date de fin de diag sans date de début
// - Date de fin de diag sans type de diag
// - Date de fin de diag sans préco d'acc.
// - Date de début d'acc. sans niv d'acc.
// - Date de fin d'acc. sans date de début
// - Date de fin d'acc. sans PAL
// - Date de fin d'acc. sans motif de fin d'acc.
// - Date de fin d'acc. sans situation résidentielle à l'issue
// - Date de propo logement sans modalité d'accès au logement
// - Date de propo logement sans origine de la propo (?)
// - Résultat de la propo sans date de propo ou sans modalité d'accès
// ...

// Validation des données de la fiche personne
export default class ValidationAvdlSupport {

    constructor() {
        this.ajaxRequest = new AjaxRequest();
        this.validationInput = new ValidationInput();
        this.select = new Select();
        this.loader = new Loader();

        this.prefix = "support_avdl_"

        this.serviceSelectElt = document.getElementById("support_service");

        this.btnSubmitElts = document.querySelectorAll("button[type='submit']");
        this.dateInputElts = document.querySelectorAll("input[type='date']");
        this.now = new Date();

        this.init();
    }

    init() {}
}