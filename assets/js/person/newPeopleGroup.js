import ValidationPerson from './validationPerson'
import SelectType from '../utils/selectType'
import ParametersUrl from '../utils/parametersUrl'

/**
 * Nouveau groupe de personnes.
 */
export default class NewPeopleGroup {
    constructor() {
        this.selectType = new SelectType()
        this.parametersUrl = new ParametersUrl()

        this.form = document.querySelector('#person>form').name // role_person_group

        this.typoInputElt = document.getElementById('role_person_people_groupGroup_familyTypology')
        this.nbPeopleInputElt = document.getElementById('role_person_people_groupGroup_nbPeople')
        this.roleInputElt = document.getElementById('role_person_role')

        if (this.typoInputElt) {
            this.roleInputElt = document.getElementById(this.form + '_role')
        }
        this.firstnameInputElt = document.getElementById(this.form + '_person_firstname')
        this.lastnameInputElt = document.getElementById(this.form + '_person_lastname')
        this.birthdateInputElt = document.getElementById(this.form + '_person_birthdate')
        this.genderInputElt = document.getElementById(this.form + '_person_gender')

        this.genderValue = null, this.typoValue = null, this.nbPeopleValue = null, this.roleValue = null
        this.init()
    }

    init() {
        if (this.genderInputElt) {
            this.genderInputElt.addEventListener('input', this.selectType.getOption.bind(this, this.genderInputElt))
        }
        if (this.typoInputElt) {
            this.typoInputElt.addEventListener('input', this.editTypo.bind(this))
            this.nbPeopleInputElt.addEventListener('input', this.editNbPeople.bind(this))
        }
        // this.phone1InputElt.addEventListener('input', this.phone.bind(this))


        let validationPerson = new ValidationPerson(
            this.form + '_person_lastname',
            this.form + '_person_firstname',
            this.form + '_person_birthdate',
            this.form + '_person_gender',
            this.form + '_person_email',
            this.form + '_role',
            this.form + '_peopleGroup_familyTypology',
            this.form + '_peopleGroup_nbPeople'
        )

        document.getElementById('send').addEventListener('click', e => {
            if (validationPerson.getNbErrors() > 0) {
                e.preventDefault(), {
                    once: true
                }
            }
        })
        this.getparametersValues()
    }

    getparametersValues() {
        let firstname = this.parametersUrl.get('firstname')
        if (firstname) {
            this.firstnameInputElt.value = decodeURI(firstname)
        }
        let lastname = this.parametersUrl.get('lastname')
        if (lastname) {
            this.lastnameInputElt.value = decodeURI(lastname)
        }
        let birthdate = this.parametersUrl.get('birthdate')
        if (birthdate) {
            this.birthdateInputElt.value = birthdate
        }
        let gender = this.parametersUrl.get('gender')
        if (gender) {
            this.selectType.setOption(this.genderInputElt, parseInt(gender))
        }
    }

    getValues() {
        this.genderValue = this.selectType.getOption(this.genderInputElt)
        this.typoValue = this.selectType.getOption(this.typoInputElt)
        this.nbPeopleValue = parseInt(this.nbPeopleInputElt.value)
        this.roleValue = this.selectType.getOption(this.roleInputElt)
    }

    editTypo() {
        this.getValues()
        switch (this.typoValue) {
            case 1:
                this.roleValue = 5
                this.nbPeopleValue = 1
                this.genderValue = 1
                break
            case 2:
                this.roleValue = 5
                this.nbPeopleValue = 1
                this.genderValue = 2
                break
            case 3:
                this.roleValue = 1
                this.nbPeopleValue = 2
                break
            case 4:
                this.roleValue = 4
                this.genderValue = 1
                break
            case 5:
                this.roleValue = 4
                this.genderValue = 2
                break
            case 6:
                this.roleValue = 1
                break
        }

        this.nbPeopleInputElt.value = this.nbPeopleValue
        this.selectType.setOption(this.roleInputElt, this.roleValue)
        this.selectType.setOption(this.genderInputElt, this.genderValue)
    }

    editNbPeople() {
        if (this.nbPeopleValue === 1 && this.genderValue === 1) {
            this.typoValue = 1
            this.roleValue = 5
        } else if (this.nbPeopleValue === 1 && this.genderValue === 2) {
            this.typoValue = 2
            this.roleValue = 5
        }

        if (this.nbPeopleValue === 1 | this.typoValue <= 2) {
            this.selectType.setOption(this.typoInputElt, this.typoValue)
        }
    }
}