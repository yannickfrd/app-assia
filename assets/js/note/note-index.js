import NoteManager from './NoteManager'
import ScrollAnimation from '../utils/scrollAnimation'

document.addEventListener('DOMContentLoaded', () => {
    new NoteManager()
    const scrollAnimation = new ScrollAnimation()
    scrollAnimation.init()
})
