// 
class Search_ajax {
    constructor() {
        this.searchElt = document.getElementById("search");
        this.resultsSearchElt = document.getElementById("results_search");
        this.countdownID = null;
        this.init();
    }

    init() {
        this.searchElt.addEventListener("keyup", this.count.bind(this));
        window.addEventListener("click", function (e) {
            this.resultsSearchElt.innerHTML = "";
        }.bind(this));
    }

    count() {
        clearInterval(this.countdownID);
        this.countdownID = setTimeout(this.ajax.bind(this), 500);
    }

    ajax() {
        let valueSearchElt = this.searchElt.value;
        let lengthSearchElt = valueSearchElt.length;
        // console.log(lengthSearchElt);
        if (lengthSearchElt > 2) {
            this.resultsSearchElt.innerHTML = "";
            let ulElt = document.createElement("ul");
            ulElt.className = "mr-2 p-2 bg-light text-dark border border-light rounded";
            let url = "/search/person?search=" + valueSearchElt;
            axios.get(url).then(function (response) {
                if (response.data.nb_results > 0) {
                    response.data.results.forEach(person => {
                        let liElt = document.createElement("li");
                        let aElt = document.createElement("a");
                        liElt.className = "bg-light pl-2 py-0";
                        aElt.textContent = person.lastname + " " + person.firstname;
                        aElt.href = "/person/" + person.id;
                        aElt.className = "font-size-10 text-dark";
                        liElt.appendChild(aElt);
                        ulElt.appendChild(liElt);
                    });
                } else {
                    let liElt = document.createElement("li");
                    liElt.className = "bg-light pl-2 py-0";
                    liElt.textContent = "Aucun résultat.";
                    ulElt.appendChild(liElt);
                }
            }).catch(function (error) {
                if (error.status === 403) {
                    // console.log("Non connecté.");
                } else {
                    // console.log("Aucun résultat.");
                }
            })
            this.resultsSearchElt.appendChild(ulElt);
        }
    }
}

// $(document).ready(function () {
//     let searchElt = document.getElementById("search");
//     searchElt.addEventListener("keyup", function () {
//             let valueSearchElt = searchElt.value;
//             let lengthSearchElt = valueSearchElt.length;
//             console.log(lengthSearchElt);

//             if (lengthSearchElt > 2) {
//                 console.log("search !");
//                 $.ajax({
//                     type: "GET",
//                     url: "result.php",
//                     data: valueSearchElt,
//                     success: function (server_response) {
//                         this.searchElt.html(server_response).show();
//                     }
//                 });
//             }
//         }

//     )
// })



let searchElt = new Search_ajax();