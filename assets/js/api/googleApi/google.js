    //
    // const {google} = require('googleapis')
    // const {OAuth2} = google.auth
    // const oAuth2Client = new OAuth2(
    //     '226888224425-jl18mogr4dpouu479j1vbjq3558e9r6k.apps.googleusercontent.com',
    //     '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'
    // )
    //
    // oAuth2Client.setCredentials({refresh_token: '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'})
    //
    // console.log(oAuth2Client)
    // const  calendar = google.calendar({version: 'v3', auth: oAuth2Client})
    //
    // const eventStartTime = new Date()

// import Ajax from '../../utils/ajax'
// // import {google} from "googleapis";
//
// import google from "googleapis";

import Ajax from "../../utils/ajax";

export default class Google {
    constructor() {
        this.ajax = new Ajax
        // this.client = null
        // this.clientId = '226888224425-jl18mogr4dpouu479j1vbjq3558e9r6k.apps.googleusercontent.com'
        // this.apiKey = 'AIzaSyBbf4Nc3NGPJ-mHysK2DkbDx-FFn2y5Xz0'
        //
        this.modalRdvElt = document.getElementById('modal-rdv')
        this.formRdvElt = this.modalRdvElt.querySelector('form[name=rdv]')
        this.checkboxGoogleElt = this.formRdvElt.elements['rdv_googleCalendar']
        this.urlCreateClientGoogle = this.checkboxGoogleElt.dataset['clientGoogle']

        this.init()
    }

    init() {

        // console.log(auth)

        // oAuth2Client.setCredentials({refresh_token: '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'})
        // const  calendar = google.calendar({version: 'v3', auth: oAuth2Client})

        // const eventStartTime = new Date()

        this.ajax.send('GET', this.urlCreateClientGoogle, this.response.bind(this))


        // const clientLoad = gapi.load('client:auth2', this.getClient);
        // console.log(clientLoad)


        // 1. Identification des authorisations
        // this.getClient()
    }

    getClient() {

        // 1. On vérifie dans les cookies si le client existe
        // if (!window.localStorage.getItem('agenda.client_google_calendar')) {
        //     return this.initClient
        // }

        // 1.1 On retourne le client
        // 1.2 Sinon on en cré un nouveau

    }

    // initClient() {
    //
    //     gapi.client.init({
    //         apiKey: this.apiKey,
    //         clientId: this.clientId,
    //         discoveryDocs: 'https://accounts.google.com/o/oauth2/auth',
    //         scope: 'https://www.googleapis.com/auth/calendar'
    //     }).then(r => {
    //
    //         console.log(r)
    //         // Listen for sign-in state changes.
    //         gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);
    //
    //         // Handle the initial sign-in state.
    //         updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
    //         authorizeButton.onclick = handleAuthClick;
    //         signoutButton.onclick = handleSignoutClick;
    //     }, function(error) {
    //         appendPre(JSON.stringify(error, null, 2));
    //     });
    // }
    // handleClientLoad() {
    //     console.log('mokjhfdslij')
    // }

    response(data) {
        console.log(data.url)

        window.open(data.url, '_blank')

    }
}