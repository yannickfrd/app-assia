// Requête Ajax pour mettre à jour les informations individuelles
class UpdatePerson {

    constructor() {
        this.personElt = document.querySelector('form[name=person]');
        this.updatePersonBtn = document.getElementById("updatePerson");
        this.url = this.updatePersonBtn.getAttribute("data-url");
        this.init();
    }

    init() {
        let validationPerson = new ValidationPerson(
            "person_lastname",
            "person_firstname",
            "person_birthdate",
            "person_gender",
            "person_email"
        );

        this.updatePersonBtn.addEventListener("click", function (e) {
            e.preventDefault();
            if (!validationPerson.getNbErrors()) {
                let formData = new FormData(this.personElt);
                let formToString = new URLSearchParams(formData).toString();
                ajaxRequest.init("POST", this.url, this.response.bind(this), true, formToString);
            } else {
                new MessageFlash("danger", "Veuillez corriger les erreurs avant de mettre à jour.");
            }
        }.bind(this));
    }

    response(data) {
        let dataJSON = JSON.parse(data);
        if (dataJSON.code === 200) {
            dataJSON.msg.forEach(msg => {
                new MessageFlash(dataJSON.alert, msg);
                if (dataJSON.alert === "success") {
                    document.getElementById("js-person-updated").textContent = "(modifié le " + dataJSON.date + " par " + dataJSON.user + ")";
                }
            });
        }
    }
}

let updatePerson = new UpdatePerson();

// let now = new Date();
// now = now.getDate() + "/" + (now.getMonth() + 1) + "/" + now.getFullYear() + " à " + now.getHours() + ":" + now.getMinutes();
// new Notification(dataJSON.alert, now, date);
// $(function () {
//     $(".toast").toast({
//         autohide: false,
//     })
// })
// $(function () {
//     $(".toast").toast("show");
// });