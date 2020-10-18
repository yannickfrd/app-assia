/**
 * Animation d'un élément au défilement
 */
export default class ScrollAnimation {

    constructor(root = null, rootMargin = '0px', ratio = 0.2) {

        this.ratio = ratio

        this.options = {
            root: this.root,
            rootMargin: this.rootMargin,
            threshold: this.ratio // à quel moment le système d'intersection est détecté
        }
    }

    /**
     * @param {String} classOff 
     * @param {String} classOn 
     */
    init(classOff = 'reveal', classOn = 'reveal-on') {
        const callback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.intersectionRatio > this.ratio) {
                    entry.target.classList.add(classOn)
                    observer.unobserve(entry.target)
                }
            })
        }

        const observer = new IntersectionObserver(callback, this.options)

        document.querySelectorAll('.' + classOff).forEach(target => {
            observer.observe(target)
        })
    }
}