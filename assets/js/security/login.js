import SeePassword from './seePassword'

new SeePassword()

// Refresh the page every 60 minutes
setInterval(() => {
    window.location.reload()
},60 * 60 * 1000)