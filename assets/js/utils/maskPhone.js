// Masque de saisie pour le numéro de téléphone
import "jquery-mask-plugin";

$(document).ready(function ($) {
    $(".js-phone").mask("99 99 99 99 99", {
        placeholder: "__ __ __ __ __"
    });
});