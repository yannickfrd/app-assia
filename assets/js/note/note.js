import AjaxRequest from "../utils/ajaxRequest";

import ShowNote from "./showNote";

let ajaxRequest = new AjaxRequest();

let showNote = new ShowNote(ajaxRequest);



// import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

// ClassicEditor
//     .create(document.querySelector('#editor'))
//     .then(editor => {
//         window.editor = editor;
//     })
//     .catch(error => {
//         console.error('There was a problem initializing the editor.', error);
//     });