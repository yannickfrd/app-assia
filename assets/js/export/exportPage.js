import AjaxRequest from '../utils/ajaxRequest'
import ExportData from './exportData'

let ajaxRequest = new AjaxRequest()

document.addEventListener('DOMContentLoaded', () => {
    new ExportData(ajaxRequest)
})