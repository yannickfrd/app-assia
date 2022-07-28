/**
 * Class pour afficher le mot de passe.
 */
export default class SeePassword {

    constructor() {
        this.usernameInputElt = document.querySelector('input[name="username"]')
        
        this.init()
    }
    
    init() {
        // Remove all spaces and tabs in username input
        if (this.usernameInputElt) {
            this.usernameInputElt.addEventListener('input', e => {
                e.target.value = e.target.value.replace(/(\s|\t){1,}/g, '')
            })
        }

        document.querySelectorAll('.js-password-group').forEach(passwordGroupElt => {
            const passwordElt = passwordGroupElt.querySelector('.js-password');
            const showPasswordElt = passwordGroupElt.querySelector('.js-show-password');
            //  Affiche du mot de passe au clic sur l'oeil
            ['mousedown', 'touchstart'].forEach(eventType => {
                showPasswordElt.addEventListener(eventType, this.see.bind(this, passwordElt))
            });
            // Masque le mot de passe au relachement de la souris ou du doigt
            ['mouseup', 'touchend'].forEach(eventType => {
                document.addEventListener(eventType, this.hide.bind(this, passwordElt))
            });
        })
    }
    
    /**
     * Affiche du mot de passe.
     * @param {HTMLElement} passwordElt 
     */
    see(passwordElt) {
        passwordElt.type = 'text'
    }

    /**
     * Masque le mot de passe.
     * @param {HTMLElement} passwordElt 
     */
    hide(passwordElt) {
        passwordElt.type = 'password'
    }
}