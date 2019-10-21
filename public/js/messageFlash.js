// Message flash
class MessageFlash {

    constructor(alert, message) {
        this.msgFlashContentElt = document.getElementById("js-notif-container");
        this.alert = alert;
        this.message = message;
        this.msg = null;
        this.init();
    }

    // Initialise le message
    init() {
        this.msg = `
            <div class="js-msg-content rounded">
                <div id="js-msg-flash" class="msg-flash alert alert-${this.alert} alert-dismissible mb-2 fade show"
                    role="alert" aria-live="assertive" aria-atomic="true">
                    <i class="fas fa-info-circle ml-1"></i>
                    <span>${this.message}</span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            `
        this.addMsg();
    }

    // Ajoute le message flash dans la div
    addMsg() {
        this.msgFlashContentElt.innerHTML = this.msg + this.msgFlashContentElt.innerHTML;
    }
}