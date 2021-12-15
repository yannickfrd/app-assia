// import Username from './username'
import Username from './username'
import SeePassword from './seePassword'
import DeleteTr from '../utils/deleteTr'
import changeChecker from '../utils/form/changeChecker'
import AddCollectionWidget from '../utils/addCollectionWidget'
import '../utils/maskPhone'
import 'select2'

document.addEventListener('DOMContentLoaded', () => {
    // let username = new Username('security_user')
    new Username('user')
    new SeePassword()
    new DeleteTr('function-table')
    new changeChecker('user') // form name

    const addCollectionWidget = new AddCollectionWidget()

    if (parseInt(document.querySelectorAll('#serviceUser-fields-list>tr').length) === 0) {
        addCollectionWidget.addElt(document.querySelector('.add-another-collection-widget'))
    }
})

$('select[data-select2-id="role"]').select2({
    placeholder: '  -- Rôle --',
    'language': {
        'noResults': () => '<span class="text-secondary">Aucun résultat.</span>'
    },
})