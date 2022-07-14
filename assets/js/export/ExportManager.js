import Ajax from '../utils/ajax'
import AlertMessage from '../utils/AlertMessage'
import Loader from '../utils/loader'
import {Tooltip} from 'bootstrap'

export default class ExportManager {

    constructor() {
        this.formElt = document.querySelector('#accordion_search>form')
        this.resultsElt = document.getElementById('results')
        this.loader = new Loader()
        this.ajax = new Ajax(this.loader, 30 * 60)
        this.init()
    }

    init() {
        this.formElt.querySelectorAll('button[type="submit"]').forEach(btnElt => {
            btnElt.addEventListener('click', e => {
                this.loader.on()
                e.preventDefault()
                this.ajax.send('POST', btnElt.dataset.path, this.response.bind(this), new FormData(this.formElt))
            })
        })

        document.querySelectorAll('button[data-action="delete_export"]').forEach(btnElt => this.initBtnDelete(btnElt))
    }

    /**
     * 
     * @param {HTMLButtonElement} btnElt 
     */
    initBtnDelete(btnElt) {
        btnElt.addEventListener('click', () => {
            if (window.confirm(btnElt.dataset.msg)) {
                this.ajax.send('GET', btnElt.dataset.path, this.response.bind(this))
            }
        })
    }

    /**
     * Réponse du serveur.
     * @param {Object} data 
     */
    response(data) {
        switch (data.action) {
            case 'count':
                this.updateResultsCounter(data.nbResults)
                break
            case 'create':
                this.createTr(data.export, data.path)
                break
            case 'export':
                this.editTr(data.export, data.path)
                break
            case 'delete':
                this.deleteTr(data.export)
                break
        }

        this.loader.off()

        if (data.msg) {
            new AlertMessage(data.alert, data.msg)
        }

        if (data.alert === 'danger') {
            console.error(data.error)
        }
    }

    /**
     * Ajoute la ligne d'export dans le corps <tbody> du tableau.
     * 
     * @param {object} exportObject
     * @param {string} path
     */
    createTr(exportObject, path) {
        const tbodyElt = document.querySelector('#table_exports>tbody')
        const rowElt = document.createElement('tr')

        let htmlContent = `
            <td scope="row" class="align-middle text-center" data-cell="export_download">
                <i class="fas fa-spinner text-dark" title="Export en cours de préparation"
                    data-bs-toggle="tooltip" data-bs-placement="right"></i>
            </td>
            <td class="align-middle" data-cell="export_title">${exportObject.title}</td>
            <td class="align-middle" data-cell="export_comment">${exportObject.comment}</td>
            <td class="align-middle text-end" data-cell="export_nbResults">
                ${parseInt(exportObject.nbResults).toLocaleString('fr')}
            </td>
            <td class="align-middle text-end" data-cell="export_size"><i class="fas fa-spinner text-dark"></i></td>
            <td class="align-middle">${exportObject.createdAtToString}</td>
            <td class="align-middle text-center">
                <button class="btn btn-danger btn-sm shadow my-1" data-action="delete_export"
                    data-path="/export/${exportObject.id}/delete"
                    title="Supprimer le fichier d'export" data-bs-toggle="tooltip" data-bs-placement="bottom" 
                    data-msg="Êtes-vous vraiment sûr de vouloir supprimer ce fichier d\'export ?">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>`

        rowElt.id = 'export_' + exportObject.id
        rowElt.innerHTML = htmlContent

        tbodyElt.insertBefore(rowElt, tbodyElt.firstChild)

        this.initBtnDelete(rowElt.querySelector('button[data-action="delete_export"]'))

        rowElt.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(elt => new Tooltip(elt))
        
        this.updateExportCounter(+1)

        this.ajax.send('POST', path, this.response.bind(this), new FormData(this.formElt))
    }

    /**
     * @param {object} exportObject
     * @param {string} path
     */
    editTr(exportObject, path) {
        const rowElt = document.querySelector('tr#export_' + exportObject.id)  

        rowElt.querySelector('td[data-cell="export_download"]').innerHTML = `
            <a href="${path}" class="btn btn-primary btn-sm shadow my-1" 
            title="Télécharger l'export" data-bs-toggle="tooltip" data-bs-placement="bottom"><i class="fas fa-file-download"></i>
            </a>`
        rowElt.querySelector('td[data-cell="export_size"]').textContent = Math.round(exportObject.size / 1000) + ' Ko'

        rowElt.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(elt => new Tooltip(elt))
    }

    /**
     * Supprime la ligne <tr> correspondant à l'eeport.
     * @param {Object} exportObject 
     */
    deleteTr(exportObject) {
        const rowElt = document.getElementById(`export_${exportObject.id}`)
        
        if (rowElt) {
            rowElt.remove()
        } else {
            console.error('No row export ' + exportObject.id + ' in this page.')
        }

        this.updateExportCounter(-1)
    }

    /**
     * @param {number} value 
     */
    updateExportCounter(value) {
        const exportCounterElt = document.getElementById('export_counter')
        exportCounterElt.textContent = parseInt(exportCounterElt.textContent) + value
    }


    /**
     * @param {number} nbResults 
     */
    updateResultsCounter(nbResults) {
        this.resultsElt.textContent = parseInt(nbResults).toLocaleString('fr') + ' résultat' + (nbResults > 0 ? 's' : '') + '.'
    }
}