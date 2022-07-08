import SearchManager from './utils/SearchManager'
import './utils/maskPhone'

document.addEventListener('DOMContentLoaded', () => {
    new SearchManager('accordion_search')
    
    const headingSearchElt = document.getElementById('headingSearch');
    
    if (headingSearchElt) {
        const spanFaElt = headingSearchElt.querySelector('span.fa');
        headingSearchElt.addEventListener('click', () => {
            if (headingSearchElt.classList.contains('collapsed')) {
                spanFaElt.classList.replace('fa-chevron-down', 'fa-chevron-right');
            } else {
                spanFaElt.classList.replace('fa-chevron-right', 'fa-chevron-down');
            }
        })
        
        if (window.innerWidth < 400) {
            headingSearchElt.click();
        }
    }
})