import SelectRadioJS from '../utils/selectRadio'
import RemoveTableRow from '../utils/removeTableRow'
import NewForm from '../utils/newForm'
import CheckChange from '../utils/checkChange'

document.addEventListener('DOMContentLoaded', () => {
    new SelectRadioJS('table-people')
    new RemoveTableRow('.js-tr-person', 'modal-confirm', 'group_nbPeople')
    new NewForm('btn-new-support', 'container-form-new-support', 'modal-new-support') // Formulaire pour la création d'un nouveau suivi
    new CheckChange('group') // form name
})