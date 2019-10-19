//
class NewGroupPeople {
    constructor() {
        this.birthdateElt = document.getElementById("role_person_group_person_birthdate");
        this.genderElt = document.getElementById("role_person_group_person_gender");
        this.typoElt = document.getElementById("role_person_group_groupPeople_familyTypology");
        this.nbPeopleElt = document.getElementById("role_person_group_groupPeople_nbPeople");
        this.roleElt = document.getElementById("role_person_group_role");

        this.genderValue = null, this.typoValue = null, this.nbPeopleValue = null, this.roleValue = null;

        this.init();
    }

    init() {
        this.birthdateElt.addEventListener("focusout", this.getBirthdate.bind(this));
        this.genderElt.addEventListener("input", this.getGender.bind(this));
        this.typoElt.addEventListener("input", this.editTypo.bind(this));
        this.nbPeopleElt.addEventListener("input", this.editNbPeople.bind(this));
    }

    getBirthdate() {
        var birthdate = new Date(this.birthdateElt.value);
        var now = new Date();
        let age = Math.round((now - birthdate) / (24 * 3600 * 1000 * 365.225));
        if (birthdate < now && age < 90) {
            console.log("Date de naissance correct");
        } else {
            console.error("Date de naissance incorrect ! (" + age + " ans)");
        }
    }

    getValues() {
        this.getGender();
        this.getTypo();
        this.getNbPeople();
        this.getRole();
    }

    getGender() {
        this.genderElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.genderValue = parseInt(option.value);
            }
        });
    }

    getTypo() {
        this.typoElt.querySelectorAll("option").forEach(option => {
            if (option.selected === true) {
                this.typoValue = parseInt(option.value);
            }
        });
    }

    setTypo(value) {
        this.typoElt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === value) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });
    }

    getNbPeople() {
        this.nbPeopleValue = parseInt(this.nbPeopleElt.value);
    }

    getRole() {
        this.roleElt.querySelectorAll("option").forEach(option => {
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
                this.roleValue = 7;
                this.nbPeopleValue = 1;
                this.genderValue = 1;
                break;
            case 2:
                this.roleValue = 7;
                this.nbPeopleValue = 1;
                this.genderValue = 2;
                break;
            case 3:
                this.roleValue = 2;
                this.nbPeopleValue = 2;
                break;
            case 4:
                this.roleValue = 6;
                this.genderValue = 1;
                break;
            case 5:
                this.roleValue = 6;
                this.genderValue = 2;
                break;
            case 6:
                this.roleValue = 2;
                break;
        }

        this.nbPeopleElt.value = this.nbPeopleValue;

        this.roleElt.querySelectorAll("option").forEach(option => {
            if (parseInt(option.value) === this.roleValue) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });

        this.setOption(this.genderElt, this.genderValue);
    }

    editNbPeople() {
        this.getValues();
        if (this.nbPeopleValue === 1 && this.genderValue === 1) {
            this.typoValue = 1;
            this.roleValue = 7;
        } else if (this.nbPeopleValue === 1 && this.genderValue === 2) {
            this.typoValue = 2;
            this.roleValue = 7;
        }

        if (this.nbPeopleValue === 1 | this.typoValue <= 2) {
            this.setOption(this.typoElt, this.typoValue);
        }
    }
}

let newGroupPeople = new NewGroupPeople();