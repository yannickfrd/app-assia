import RdvManager from "./RdvManager";
import Alert from "../../alert/alert";
// import SearchLocation from '../utils/searchLocation'

window.addEventListener('load', () => {
    new RdvManager()
// new SearchLocation('rdv_search_location')
    new Alert()
})
