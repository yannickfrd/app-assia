// Message flash
class Notification {

    constructor(alert, message, datetime) {
        this.msgFlashContentElt = document.getElementById("js-msg-container");
        this.alert = alert;
        this.datetime = datetime;
        this.message = message;
        this.toast = null;
        this.init();
    }

    // Initialise le message
    init() {
        this.toast = `
                <div id="myToast" class="toast alert alert-${this.alert}" role="alert" aria-live="assertive" aria-atomic="true" autohide="false">
                    <div class="toast-header">
                        <span class ="fas fa-info-circle pr-2"></class>
                        <strong class="mr-auto">Notification</strong>
                        <small>${this.datetime}</small>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">${this.message}</div>
                </div>
            `
        this.addMsg();
    }

    // Ajoute le message flash dans la div
    addMsg() {
        this.msgFlashContentElt.innerHTML = this.toast + this.msgFlashContentElt.innerHTML;
    }
}