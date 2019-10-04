jQuery(document).ready(function ($) {
    // au clic sur un lien précédé d'un dièse hashtag
    $('a[href^="#"]').on('click', function (e) {
        // enregistre la valeur de l'attribut  href dans la variable target
        let target = $(this).attr('href');
        /* 	- le sélecteur $(html, body) permet de corriger un bug sur chrome 
       			et safari (webkit) 	
       		- on arrête toutes les animations en cours 
	   		- on fait maintenant l'animation vers le haut (scrollTop) vers 
	   			notre ancre target 
        */
        $("html, body").stop().animate({
            scrollTop: $(target).offset().top
        }, 1000);
        // bloquer le comportement par défaut: on ne rechargera pas la page 
        // mais on ne changera pas non plus l'url dans la barre de navigation 
        e.preventDefault();
    });
});