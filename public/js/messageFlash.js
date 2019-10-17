// Message flash
class MessageFlash {

    constructor(alert, message) {
        this.msgFlashContentElt = document.getElementById("js-msg-flash-content");
        this.alert = alert;
        this.message = message;
        this.msg = null;
        this.init();
    }

    // Initialise le message
    init() {
        this.msg = `
            <div id="js-msg-flash" class="alert alert-${this.alert} alert-dismissible mb-3 fade show" role="alert">
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
        this.msgFlashContentElt.innerHTML = this.msgFlashContentElt.innerHTML + this.msg;
    }
}