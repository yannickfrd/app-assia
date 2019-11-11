import "jquery-mask-plugin";

$(document).ready(function ($) {
    $(".js-zip-code").mask("99 999", {
        placeholder: "__ ___"
    });
});