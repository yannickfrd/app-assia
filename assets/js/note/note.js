import AjaxRequest from "../utils/ajaxRequest";

import EditNote from "./editNote";

let ajaxRequest = new AjaxRequest();

let editNote = new EditNote(ajaxRequest);

// import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

// ClassicEditor
//     .create(document.querySelector('#editor'))
//     .then(editor => {
//         window.editor = editor;
//     })
//     .catch(error => {
//         console.error('There was a problem initializing the editor.', error);
//     });