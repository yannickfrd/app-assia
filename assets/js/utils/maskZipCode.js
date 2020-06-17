import "jquery-mask-plugin";

$(document).ready(function ($) {
    $(".js-zipcode").mask("99 999", {
        placeholder: "__ ___"
    });
});