// import ValidationPerson from './validationPerson'
import UpdatePerson from './updatePerson'
import NewForm from '../utils/newForm'
import NewPeopleGroup from './newPeopleGroup'
import CheckChange from '../utils/checkChange'
import '../utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    const editMode = document.getElementById('person').dataset.editMode
    if (editMode === 'true') {
        new UpdatePerson()
        new CheckChange('person') // form name
    } else {
        new NewPeopleGroup()
        new CheckChange('person_role_person') // form name
    }
    if (document.getElementById('js-people')) {
        new NewForm('btn-new-support', 'container-form-new-support', 'modal-new-support') // Formulaire pour la création d'un nouveau suivi
    }
})