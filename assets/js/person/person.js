// import ValidationPerson from './validationPerson'
import UpdatePerson from './updatePerson'
import NewForm from '../utils/newForm'
import NewGroupPeople from './newGroupPeople'
import ParametersUrl from '../utils/parametersUrl'
import CheckChange from '../utils/checkChange'
import '../utils/maskPhone'

let parametersUrl = new ParametersUrl()

document.addEventListener('DOMContentLoaded', () => {
    let editMode = document.getElementById('person').dataset.editMode
    if (editMode === 'true') {
        new UpdatePerson()
        new CheckChange('person') // form name
        new NewForm()
    } else {
        new NewGroupPeople(parametersUrl)
        // new CheckChange('role_person_group') // form name
    }
})