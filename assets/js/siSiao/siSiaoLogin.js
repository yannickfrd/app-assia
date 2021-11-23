import Loader from '../utils/loader'
import Ajax from '../utils/ajax'
import MessageFlash from '../utils/messageFlash'
import { Modal } from 'bootstrap'
import SeePassword from '../security/seePassword'

/**
 * Connexion à l'API SI-SIAO
 */
export default class SiSiaoLogin {
  
    constructor() {
        this.isInitialized = false
        this.isConnected = false
    }

    /**
     * @param {String} siSiaoLoginCheckbox 
     */
    init(siSiaoLoginCheckbox) {
        this.siSiaoLoginCheckboxElt = document.getElementById(siSiaoLoginCheckbox)
        
        if (this.isInitialized || null == this.siSiaoLoginCheckboxElt) {
            return
        }
        
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader)
        new SeePassword()
        

        this.siSiaoLoginModalElt = new Modal(document.getElementById('modal-si-siao-login'))
        this.siSiaoLoginFormElt = document.querySelector('form[name="si_siao_login"')
        this.sisiaoConnectionBtnElt = document.getElementById('si-siao-connection')

        this.siSiaoLoginCheckboxElt.addEventListener('click', e => this.onClickSiSiaoSearch(e))
        
        this.sisiaoConnectionBtnElt.addEventListener('click', e => {
            e.preventDefault()
            this.tryLogin()
        })

        this.isInitialized = true
    }

    onClickSiSiaoSearch(e) {
        if (this.loader.isActive()) {
            return e.preventDefault()
        }

        if (this.siSiaoLoginCheckboxElt.checked) {
            e.preventDefault()
            return this.checkConnection()
        }
    }

    checkConnection() {
        if (null === this.siSiaoLoginCheckboxElt) {
            return null
        }

        const url = this.sisiaoConnectionBtnElt.dataset.url
        this.ajax.send('GET', url, this.responseCheckConnection.bind(this))
    }

    /**
     * Donne la réponse à la requête Ajax.
     * @param {Object} data 
     */
    responseCheckConnection(data) {
        if (data.alert && true === data.isConnected) {
            this.isConnected = true
            this.siSiaoLoginCheckboxElt.checked = true
            return new MessageFlash(data.alert, data.msg)
        }
        this.isConnected = false
        this.showModal()
    }

    showModal() {
        this.siSiaoLoginModalElt.show()
    }

    tryLogin() {
        const url = this.siSiaoLoginFormElt.action
        this.ajax.send('POST', url, this.response.bind(this), new FormData(this.siSiaoLoginFormElt))
    }

    /**
     * Donne la réponse à la requête Ajax.
     * @param {Object} data 
     */
    response(data) {
        if (data.alert && data.alert === 'success') {
            this.siSiaoLoginModalElt.hide()
            this.siSiaoLoginCheckboxElt.checked = true
            this.isConnected = true
        } else {
            this.isConnected = false
            console.error(data)
            
        }

        new MessageFlash(data.alert, data.msg)
    }
}
