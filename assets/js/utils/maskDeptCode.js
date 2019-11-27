import "jquery-mask-plugin";

$(document).ready(function ($) {
    $(".js-dept-code").mask("99", {
        placeholder: "__"
    });
});