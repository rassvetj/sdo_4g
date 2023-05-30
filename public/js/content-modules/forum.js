if(!window.rels) window.rels={}
window.rels.pcard={title:"\u041a\u0430\u0440\u0442\u043e\u0447\u043a\u0430 \u043f\u043e\u043b\u044c\u0437\u043e\u0432\u0430\u0442\u0435\u043b\u044f"} 
$(document)
    .undelegate('a.lightbox', 'click.pcard')
    .delegate('a.lightbox', 'click.pcard', function (event) {
        var currel = $(event.currentTarget).attr('rel');
        $(event.currentTarget).lightdialog({
            title: window.rels[currel].title,
            dialogClass: "pcard",
            rel: "a.lightbox[rel='"+currel+"']",
            l10n: {
            prev: "\u041d\u0430\u0437\u0430\u0434",
            next: "\u0412\u043f\u0435\u0440\u0451\u0434" }
        }).lightdialog("open");
        event.preventDefault();
        event.stopImmediatePropagation();
    });