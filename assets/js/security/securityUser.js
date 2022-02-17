// import Username from './username'
import Username from './username'
import SeePassword from './seePassword'
import DeleteTr from '../utils/deleteTr'
import changeChecker from '../utils/form/changeChecker'
import WidgetCollectionManager from '../utils/form/WidgetCollectionManager'
import '../utils/maskPhone'
import SelectManager from '../utils/form/SelectManager'

document.addEventListener('DOMContentLoaded', () => {
    new Username('user')
    new SeePassword()
    new DeleteTr('function-table')
    new changeChecker('user') // form name
    new SelectManager('#user_roles')

    const widgetCollectionManager = new WidgetCollectionManager()

    if (parseInt(document.querySelector('#serviceUser-fields-list').children.length) === 0) {
        widgetCollectionManager.addElt(document.querySelector('button[data-add-widget]'))
    }
})