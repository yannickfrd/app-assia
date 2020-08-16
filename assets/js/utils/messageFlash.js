/**
 * Message flash.
 */
export default class MessageFlash {

    constructor(alert = 'info', message, delay = 8) {
        this.msgFlashContentElt = document.getElementById('js-notif-container')
        this.alert = alert
        this.message = message
        this.delay = delay
        this.time = 0
        this.msg = this.createMsgElt()
        this.init()
    }

    /**
     * Initialise le message.
     */
    init() {
        this.timerID = setInterval(this.timer.bind(this), 1000)
        this.msgFlashContentElt.classList.replace('d-none', 'd-block')
        this.msgFlashContentElt.insertBefore(this.msg, this.msgFlashContentElt.firstChild)

        this.msg.querySelector('button.close').addEventListener('click', () => {
            if (this.msgFlashContentElt.querySelectorAll('button.close').length === 1) {
                this.msg.remove()
                this.msgFlashContentElt.classList.replace('d-block', 'd-none')
            }
        })
    }

    /**
     * Créé l'élément du message.
     * @return {HTMLDivElement}
     */
    createMsgElt() {
        let msgElt = document.createElement('div')
        msgElt.className = 'msg-content rounded'
        msgElt.innerHTML =
            `<div id='js-msg-flash' class='mb-2 msg-flash alert alert-${this.alert} alert-dismissible fade show align-items-center'
                role='alert' aria-live='assertive' aria-atomic='true'>
                <div>${this.message}</div>
                <button type='button' id='btn-close-msg' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times</span>
                </button>
                <span id='timeline' class='bg-${this.alert}'></span>
            </div>`
        return msgElt
    }

    /**
     * Timer.
     */
    timer() {
        if (this.time > this.delay) {
            clearInterval(this.timerID)
            this.msg.remove()
        }
        this.time++
    }
}