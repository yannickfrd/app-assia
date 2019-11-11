// Message flash
export default class MessageFlash {

    constructor(alert, message) {
        this.msgFlashContentElt = document.getElementById("js-notif-container");
        this.alert = alert;
        this.message = message;
        this.msg = null;
        this.time = 0;
        this.init();
    }

    // Initialise le message
    init() {
        this.msg = document.createElement("div");
        this.msg.className = "js-msg-content rounded";
        this.msg.innerHTML = `
                <div id="js-msg-flash" class="msg-flash alert alert-${this.alert} alert-dismissible mb-2 fade show"
                    role="alert" aria-live="assertive" aria-atomic="true">
                    <i class="fas fa-info-circle ml-1"></i>
                    <span>${this.message}</span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `
        this.timerID = setInterval(this.timer.bind(this), 1000);
        this.msgFlashContentElt.appendChild(this.msg);
    }

    timer() {
        this.time++;
        if (this.time > 10) {
            clearInterval(this.timerID);
            this.msg.remove();
        }
    }
}