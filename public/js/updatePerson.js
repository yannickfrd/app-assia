// Requête Ajax pour mettre à jour les informations de la personne
class UpdatePerson {

    constructor() {
        this.personElt = document.querySelector('form[name=person]');
        this.updatePersonBtn = document.getElementById("updatePerson");
        this.url = this.updatePersonBtn.getAttribute("data-url");
        this.init();
    }

    init() {
        this.updatePersonBtn.addEventListener("click", function (e) {
            e.preventDefault();
            let formData = new FormData(this.personElt);
            let formToString = new URLSearchParams(formData).toString();
            ajaxRequest.init("POST", this.url, this.response.bind(this), true, formToString);
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



// let inputsElt = document.person.querySelectorAll("input, textarea");
// inputsElt.forEach(input => {
//     if (input.type != "submit") {
//         let key = input.id.replace("person_", "");
//         this.data += key + "=" + input.value + "&";
//         form.append(key, input.value);
//     }
// });
// let form2 = $("form[name=person]").serialize();