import 'select2'
import '../utils/maskZipcode'
import '../utils/maskPhone'

let select2Array = {
    'service': 'Service',
    'device': 'Dispositif',
}

for (let i in select2Array) {
    $('select.multi-select.js-' + i).select2({
        // theme: 'bootstrap4',
        placeholder: '  -- ' + select2Array[i] + ' --',
    })
}