import SupportPayments from "./SupportPayments"
import StopWatch from "../utils/stopWatch"

const stopWatch = new StopWatch()
document.addEventListener("DOMContentLoaded", () => {
    stopWatch.start()
    new SupportPayments()
    stopWatch.stop()
})