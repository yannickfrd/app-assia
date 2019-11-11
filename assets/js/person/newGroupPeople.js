import ValidationPerson from "./validationPerson";

//
export default class NewGroupPeople {
    constructor() {
<<<<<<< HEAD
=======
        this.lastnameInputElt = document.getElementById("role_person_group_person_lastname");
        this.firstnameInputElt = document.getElementById("role_person_group_person_firstname");
        this.birthdateInputElt = document.getElementById("role_person_group_person_birthdate");
>>>>>>> test
        this.birthdateInputElt = document.getElementById("role_person_group_person_birthdate");
        this.genderInputElt = document.getElementById("role_person_group_person_gender");
        this.typoInputElt = document.getElementById("role_person_group_groupPeople_familyTypology");
        this.nbPeopleInputElt = document.getElementById("role_person_group_groupPeople_nbPeople");
        this.roleInputElt = document.getElementById("role_person_group_role");
        // this.emailInputElt = document.getElementById("role_person_group_person_email");
<<<<<<< HEAD
        // this.phone1InputElt = document.getElementById("role_person_group_person_phone1");
=======
        this.phone1InputElt = document.getElementById("role_person_group_person_phone1");
>>>>>>> test
        this.genderValue = null, this.typoValue = null, this.nbPeopleValue = null, this.roleValue = null;
        this.init();
    }

    init() {
        this.birthdateInputElt.addEventListener("focusout", this.getAge.bind(this));
        this.genderInputElt.addEventListener("input", this.getGender.bind(this));
        this.typoInputElt.addEventListener("input", this.editTypo.bind(this));
        this.nbPeopleInputElt.addEventListener("input", this.editNbPeople.bind(this));
        // this.emailInputElt.addEventListener("focusout", this.checkEmail.bind(this));
        // this.phone1InputElt.addEventListener("input", this.phone.bind(this));
        let validationPerson = new ValidationPerson(
            "role_person_group_person_lastname",
            "role_person_group_person_firstname",
            "role_person_group_person_birthdate",
            "role_person_group_person_gender",
            "role_person_group_person_email",
            "role_person_group_role",
            "role_person_group_groupPeople_familyTypology",
            "role_person_group_groupPeople_nbPeople"
        );

        document.getElementById("send").addEventListener("click", function (e) {
            if (validationPerson.getNbErrors()) {
                e.preventDefault(), {
                    once: true
                };
                new MessageFlash("danger", "Veuillez corriger les erreurs avant d'enregistrer.");
            }
        }.bind(this));

<<<<<<< HEAD
=======
        let searchParams = new URLSearchParams(window.location.search);
        for (let param of searchParams) {
            switch (param[0]) {
                case "lastname":
                    this.lastnameInputElt.value = param[1];
                    break;
                case "firstname":
                    this.firstnameInputElt.value = param[1];
                    break;
                case "birthdate":
                    this.birthdateInputElt.value = param[1];
                    break;
                case "phone":
                    this.phone1InputElt.value = param[1];
                    break;
            }
        }
>>>>>>> test
    }


    getValues() {
        this.getGender();
        this.getTypo();
        this.getNbPeople();
        this.getRole();
    }

    getAge() {
        let birthdate = new Date(this.birthdateInputElt.value);
        let now = new Date();
        let age = Math.round((now - birthdate) / (24 * 3600 * 1000 * 365.25));
        if (age < 18) {
            this.nbPeopleValue = 3;
            this.setOption(this.roleInputElt, this.nbPeopleValue);
        }
    }

    getGender() {
        this.genderInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.genderValue = parseInt(option.value);
            }
        });
    }

    getTypo() {
        this.typoInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.typoValue = parseInt(option.value);
            }
        });
    }

    setTypo(value) {
        this.typoInputElt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === value) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });
    }

    getNbPeople() {
        this.nbPeopleValue = parseInt(this.nbPeopleInputElt.value);
    }

    getRole() {
        this.roleInputElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.roleValue = parseInt(option.value);
            }
        });
    }

    setOption(elt, value) {
        elt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === value) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });
    }

    editTypo() {
        this.getValues();
        switch (this.typoValue) {
            case 1:
                this.roleValue = 5;
                this.nbPeopleValue = 1;
                this.genderValue = 1;
                break;
            case 2:
                this.roleValue = 5;
                this.nbPeopleValue = 1;
                this.genderValue = 2;
                break;
            case 3:
                this.roleValue = 1;
                this.nbPeopleValue = 2;
                break;
            case 4:
                this.roleValue = 4;
                this.genderValue = 1;
                break;
            case 5:
                this.roleValue = 4;
                this.genderValue = 2;
                break;
            case 6:
                this.roleValue = 1;
                break;
        }

        this.nbPeopleInputElt.value = this.nbPeopleValue;

        this.roleInputElt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === this.roleValue) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });

        this.setOption(this.genderInputElt, this.genderValue);
    }

    editNbPeople() {
        if (this.nbPeopleValue === 1 && this.genderValue === 1) {
            this.typoValue = 1;
            this.roleValue = 5;
        } else if (this.nbPeopleValue === 1 && this.genderValue === 2) {
            this.typoValue = 2;
            this.roleValue = 5;
        }

        if (this.nbPeopleValue === 1 | this.typoValue <= 2) {
            this.setOption(this.typoInputElt, this.typoValue);
        }
    }
}