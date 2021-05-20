import Search from './utils/search'
import './utils/maskPhone'
import 'select2'
// import {Collapse} from 'bootstrap'

// import MaskInput from './utils/maskInput'
// new MaskInput('.js-phone')

const items = {
    'typology': 'Typologie familiale',
    'status': 'Statut',
    'poles': 'Pôles',
    'services': 'Services',
    'sub-services': 'Sous-services',
    'devices': 'Dispositifs',
    'referents': 'Référents',
    'hotels': 'Hôtel',
    'payment-type': 'Type',
    'support-type': 'Type d\'acc.',
    'levelSupport': 'Niveau d\'intervention',
};

for (let i in items) {
    $(`select[data-select2-id='${i}']`).select2({
        placeholder: '  -- ' + items[i] + ' --',
        // theme: 'bootstrap4',
        'language': {
            'noResults': () => {
                return '<span class="text-secondary">Aucun résultat.</span>'
            }
        },
        escapeMarkup: (markup) => {
            return markup
        }
    });
}

new Search('form-search')

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
    // const bsCollapse = new Collapse(headingSearchElt)
    if (window.innerWidth < 400) {
        headingSearchElt.click();
    }
}
