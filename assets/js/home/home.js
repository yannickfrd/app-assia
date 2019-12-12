 document.querySelectorAll("div.card-header").forEach(cardHeaderElt => {
     let spanFaElt = cardHeaderElt.querySelector("span.fa");
     cardHeaderElt.addEventListener("click", function () {
         if (cardHeaderElt.classList.contains("collapsed")) {
             spanFaElt.classList.replace("fa-chevron-right", "fa-chevron-down");
         } else {
             spanFaElt.classList.replace("fa-chevron-down", "fa-chevron-right");
         }
     });
 });