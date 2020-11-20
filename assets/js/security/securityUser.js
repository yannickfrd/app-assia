// import Username from './username'
import Username from './username'
import SeePassword from './seePassword'
import DeleteTr from '../utils/deleteTr'
import CheckChange from '../utils/checkChange'
import AddCollectionWidget from '../utils/addCollectionWidget'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    // let username = new Username('security_user')
    new Username('user')
    new SeePassword()
    new DeleteTr('function-table')
    new CheckChange('user') // form name

    const addCollectionWidget = new AddCollectionWidget()

    if (parseInt(addCollectionWidget.counter) === 0) {
        addCollectionWidget.addElt(document.querySelector('.add-another-collection-widget'))
    }
})