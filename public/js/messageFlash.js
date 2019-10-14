// Message flash
class MessageFlash {

    constructor(alert, message) {
        this.msgFlashElt = document.getElementById("msg-flash");
        this.alert = alert;
        this.message = message;
        this.msg = null;
        this.init();
    }

    // Initialise le message
    init() {
        this.msg = `
            <div id="msg-flash" class="alert ${this.alert} alert-dismissible mb-3 fade show" role="alert">
                <span>${this.message}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            `
        this.addMsg();
    }

    // Ajoute le message flash dans la div
    addMsg() {
        this.msgFlashElt.innerHTML = this.msgFlashElt.innerHTML + this.msg;
    }
}