// Requête AJAX
class AjaxRequest {

    constructor() {
        this.xhr = new XMLHttpRequest();
        this.timeSend = null;
        this.timeResp = null;
    }

    init(method, url, async) {
        this.timeSend = Date.now(); // Test
        this.xhr.open(method, url, async);
        this.xhr.onload = this.response.bind(this);
        this.xhr.send();

    }
    // Retourne le résultat de la rêquête
    response() {
        let readyState;
        switch (this.xhr.readyState) {
            case 0:
                readyState = "UNSENT";
                break;
            case 1:
                readyState = "OPENED";
                break;
            case 2:
                readyState = "HEADERS_RECEIVED";
                break;
            case 3:
                readyState = "LOADING";
                break;
            case 4:
                readyState = "DONE";
                break;
        }
        if (this.xhr.readyState === 4 && this.xhr.status === 200) {
            this.timeResp = Date.now();
            let time = (this.timeResp - this.timeSend) / 1000;
            console.log("Etat: " + this.xhr.readyState + "(" + readyState + "), Statut: " + this.xhr.status + ", Durée : " + time + "s");
            // let data = JSON.parse(this.xhr.responseText);
        }
        return this.xhr;
    }
}

// axios.get(url).then(function (response) {
//     let response.data
// }).catch(function (error) {
//     if (error.status === 403) {} 
//     else {}
// })