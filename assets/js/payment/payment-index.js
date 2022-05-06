import PaymentManager from "./PaymentManager"
import StopWatch from "../utils/stopWatch"

const stopWatch = new StopWatch()
window.addEventListener('load', () => {
    stopWatch.start()
    new PaymentManager()
    stopWatch.stop()
})