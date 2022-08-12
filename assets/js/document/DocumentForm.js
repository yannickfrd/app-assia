import AbstractForm from '../utils/form/AbstractForm'
import DocumentManager from "./DocumentManager"

export default class DocumentForm extends AbstractForm 
{
    /**
     *  @param {DocumentManager} manager
     */
    constructor(manager) {
        super(manager)
    }
}