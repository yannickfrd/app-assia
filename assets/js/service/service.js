import DeleteTr from '../utils/deleteTr'
import changeChecker from '../utils/form/changeChecker'
import AddCollectionWidget from '../utils/addCollectionWidget'
import UpdateService from './updateService'
import SearchLocation from '../utils/searchLocation'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    new DeleteTr('function-table')
    new changeChecker('service') // form name
    new AddCollectionWidget()
    new UpdateService()
    new SearchLocation('service_location')
})