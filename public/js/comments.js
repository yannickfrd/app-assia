class Comments {
    constructor() {
        this.commentsElt = document.querySelectorAll(".comment"); // compte le nombre de commentaires
        this.init();
    }

    init() {
        this.commentsElt.forEach(function (commentElt) {
            let idComment = Number(commentElt.id.replace("comment-", ""));
            let rectCommentContentElt = commentElt.querySelector(".comment-content").getBoundingClientRect();
            let comment = {
                content: document.getElementById("comment-content-" + idComment),
                fadeout: document.getElementById("comment-fadeout-" + idComment),
                aEdit: document.getElementById("comment-edit-" + idComment),
                form: document.getElementById("comment-form-" + idComment),
                cancelBtn: document.getElementById("comment-form-cancel-" + idComment),
                reduce: true
            };

            // Masque le contenu du commentaire quand celui dépasse les 200px de hauteur
            if (rectCommentContentElt.height > 140) {
                this.reduceOrSee(comment);
                comment.content.style.cursor = "pointer";
            }
            // Affiche ou réduit le commentaire au clic le contenu du commentaire
            comment.content.addEventListener("click", this.reduceOrSee.bind(this, comment));
            // Masque le formulaire de commentaire au clic sur le bouton "Annuler"
            comment.cancelBtn.addEventListener("click", function (e) {
                e.preventDefault();
                this.cancel(comment);
            }.bind(this));
            // Affiche le formulaire de commentaire au clic sur le lien "Modifier"
            if (comment.aEdit) {
                comment.aEdit.addEventListener("click", this.edit.bind(this, comment));
            }
        }.bind(this));
    }
    // Affiche ou réduit le commentaire
    reduceOrSee(comment) {
        if (comment.reduce === true) {
            comment.content.style.maxHeight = "140px";
            comment.content.style.overflow = "hidden";
            comment.fadeout.className = "comment-fadeout d-block";
            comment.reduce = false;
        } else {
            comment.content.style.maxHeight = "2000px";
            comment.content.style.overflow = "";
            comment.fadeout.className = "comment-fadeout d-none";
            comment.content.style.transition = "max-height 0.5s ease";
            comment.reduce = true;
        }
    }
    // Affiche le formulaire de commentaire
    edit(comment) {
        comment.content.className = "comment-content mt-2 d-none";
        comment.aEdit.className = "comment-edit d-none";
        comment.form.className = "comment-form d-block";
    }
    // Masque le formulaire de commentaire
    cancel(comment) {
        comment.content.className = "comment-content mt-2";
        comment.aEdit.className = "comment-edit";
        comment.form.className = "comment-form d-none";
    }
}