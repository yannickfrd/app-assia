import ValidationPerson from "./validationPerson";

//
export default class NewGroupPeople {
    constructor(parametersUrl) {
        this.parametersUrl = parametersUrl;
        this.form = document.querySelector("#person>form").name; // role_person_group

        this.typoInputElt = document.getElementById("role_person_group_groupPeople_familyTypology");
        this.nbPeopleInputElt = document.getElementById("role_person_group_groupPeople_nbPeople");
        this.roleInputElt = document.getElementById("role_person_role");

        if (this.typoInputElt) {
            this.roleInputElt = document.getElementById(this.form + "_role");
        }
        this.firstnameInputElt = document.getElementById(this.form + "_person_firstname");
        this.lastnameInputElt = document.getElementById(this.form + "_person_lastname");
        this.birthdateInputElt = document.getElementById(this.form + "_person_birthdate");
        this.genderInputElt = document.getElementById(this.form + "_person_gender");
        // this.emailInputElt = document.getElementById(this.form + "email");
        // this.phone1InputElt = document.getElementById(this.form + "phone1");

        this.genderValue = null, this.typoValue = null, this.nbPeopleValue = null, this.roleValue = null;
        this.init();
    }

    init() {
        if (this.birthdateInputElt) {
            this.birthdateInputElt.addEventListener("focusout", this.getAge.bind(this));
        }
        if (this.genderInputElt) {
            this.genderInputElt.addEventListener("input", this.getGender.bind(this));
        }
        if (this.typoInputElt) {
            this.typoInputElt.addEventListener("input", this.editTypo.bind(this));
            this.nbPeopleInputElt.addEventListener("input", this.editNbPeople.bind(this));
        }
        // this.emailInputElt.addEventListener("focusout", this.checkEmail.bind(this));
        // this.phone1InputElt.addEventListener("input", this.phone.bind(this));


        let validationPerson = new ValidationPerson(
            this.form + "_person_lastname",
            this.form + "_person_firstname",
            this.form + "_person_birthdate",
            this.form + "_person_gender",
            this.form + "_person_email",
            this.form + "_role",
            this.form + "_groupPeople_familyTypology",
            this.form + "_groupPeople_nbPeople"
        );

        document.getElementById("send").addEventListener("click", e => {
            if (validationPerson.getNbErrors()) {
                e.preventDefault(), {
                    once: true
                };
                new MessageFlash("danger", "Veuillez corriger les erreurs avant d'enregistrer.");
            }
        });

        let firstname = this.parametersUrl.get("firstname");
        if (firstname) {
            this.firstnameInputElt.value = decodeURI(firstname);
        }
        let lastname = this.parametersUrl.get("lastname");
        if (lastname) {
            this.lastnameInputElt.value = decodeURI(lastname);
        }
        let birthdate = this.parametersUrl.get("birthdate");
        if (birthdate) {
            this.birthdateInputElt.value = birthdate;
        }
        let gender = this.parametersUrl.get("gender");
        if (gender) {
            this.setOption(this.genderInputElt, parseInt(gender));
        }
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