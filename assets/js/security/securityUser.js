// import Username from './username'
import Username from './username'
import SeePassword from './seePassword'
import DeleteTr from '../utils/deleteTr'
import changeChecker from '../utils/form/changeChecker'
import AddCollectionWidget from '../utils/addCollectionWidget'
import '../utils/maskPhone'
import SelectManager from '../utils/form/SelectManager'

document.addEventListener('DOMContentLoaded', () => {
    new Username('user')
    new SeePassword()
    new DeleteTr('function-table')
    new changeChecker('user') // form name
    new SelectManager('#user_roles')

    const addCollectionWidget = new AddCollectionWidget()

    if (parseInt(document.querySelectorAll('#serviceUser-fields-list>tr').length) === 0) {
        addCollectionWidget.addElt(document.querySelector('.add-another-collection-widget'))
    }
})