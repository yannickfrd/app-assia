/** 
 * Smooth Scroll animation
 */
function smoothScrollTop(delay) {
    document.getElementById('scroll-top').addEventListener('click', function (e) {
        e.preventDefault()
        let target = this.getAttribute('href')
        $('html, body').stop().animate({
            scrollTop: $(target).height()
        }, delay)
    })
}