// import ValidationPerson from './validationPerson'
import UpdatePerson from './updatePerson'
import NewForm from '../utils/form/newForm'
import NewPeopleGroup from './newPeopleGroup'
import changeChecker from '../utils/form/changeChecker'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    const editMode = document.getElementById('person').dataset.editMode
    if (editMode === 'true') {
        new UpdatePerson()
        new changeChecker('person') // form name
    } else {
        new NewPeopleGroup()
        new changeChecker(document.querySelector('#person>form').name) // form name
    }
    if (document.getElementById('js-people')) {
        new NewForm('btn-new-support', 'container-form-new-support', 'modal-new-support') // Formulaire pour la création d'un nouveau suivi
    }
})