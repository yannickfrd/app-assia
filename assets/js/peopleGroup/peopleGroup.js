import RadioSelecter from '../utils/form/radioSelecter'
import RemoveTableRow from '../utils/removeTableRow'
import NewForm from '../utils/form/newForm'
import changeChecker from '../utils/form/changeChecker'
import AutoSizer from '../utils/form/autoSizer'

document.addEventListener('DOMContentLoaded', () => {
    new RadioSelecter('table-people')
    new RemoveTableRow('.js-tr-person', 'modal-confirm', 'group_nbPeople')
    new NewForm('btn-new-support', 'container-form-new-support', 'modal-new-support') // Formulaire pour la création d'un nouveau suivi
    new changeChecker('group') // form name
    new AutoSizer('textarea')
})