import RadioSelecter from '../utils/form/radioSelecter'
import ConfirmAction from '../utils/confirmAction'
import NewForm from '../utils/form/newForm'
import changeChecker from '../utils/form/changeChecker'
import AutoSizer from '../utils/form/autoSizer'
import  '../utils/maskNumber'

document.addEventListener('DOMContentLoaded', () => {
    new RadioSelecter('table-people')
    new ConfirmAction('.js-tr-person', 'modal-confirm')
    new NewForm('btn-new-support', 'container-form-new-support', 'modal-new-support') // Formulaire pour la création d'un nouveau suivi
    new changeChecker('group') // form name
    new AutoSizer('textarea')
})