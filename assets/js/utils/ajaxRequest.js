export default class AjaxRequest {

    constructor() {
        this.xhr = new XMLHttpRequest();
        // this.timeSend = null; // Temp pour test
        // this.timeResp = null; // Temp pour test
        // this.data = null;
    }

    init(method, url, callback, async, data) {
        // this.timeSend = Date.now(); // Temp pour test
        this.xhr.open(method, url, async);

        if (method === "POST") {
            this.xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            this.data = data;
        }

        this.xhr.addEventListener("load", this.load.bind(this, callback), {
            once: true
        });
        this.xhr.addEventListener("error", this.error.bind(this, url));
        // this.xhr.onload = this.response.bind(this);
        this.xhr.send(this.data);
    }

    // Retourne le résultat de la rêquête
    load(callback, url) {
        if (this.xhr.status >= 200 && this.xhr.status < 400) {
            callback(this.xhr.responseText); // Appelle la fonction callback en lui passant la réponse de la requête
            // this.timeResp = Date.now(); // Temp pour test
            // let time = (this.timeResp - this.timeSend) / 1000; // Temp pour test
            // console.log("Statut: " + this.xhr.status + ", Durée : " + time + "s");
        } else {
            console.error("Statut: " + this.xhr.status + " " + this.xhr.statusText + " " + url);
        }
    }

    error(url) {
        console.error("Statut: " + this.xhr.status + " : Erreur réseau avec l'URL " + url);
    }
}