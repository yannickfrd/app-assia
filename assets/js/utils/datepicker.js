! function (a) {
    a.fn.datepicker.dates.fr = {
        days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
        daysShort: ["Dim.", "Lun.", "Mar.", "Mer.", "Jeu.", "Ven.", "Sam."],
        daysMin: ["Dm", "Lu", "Ma", "Me", "Ju", "Ve", "Sa"],
        months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
        monthsShort: ["Janv.", "Févr.", "Mars", "Avril", "Mai", "Juin", "Juil.", "Août", "Sept.", "Oct.", "Nov.", "Déc."],
        today: "Aujourd'hui",
        monthsTitle: "Mois",
        clear: "Effacer",
    }
}(jQuery);

$(document).ready(function () {
    $(".datepicker").datepicker({
        format: "dd/mm/yyyy",
        weekStart: 1,
        language: "fr",
        todayHighlight: true,
        autoclose: true,
        assumeNearbyYear: true,
    });
});