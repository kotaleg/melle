function getURLVar(key) {
    var value = [];

    var query = String(document.location).split('?');

    if (query[1]) {
        var part = query[1].split('&');

        for (i = 0; i < part.length; i++) {
            var data = part[i].split('=');

            if (data[0] && data[1]) {
                value[data[0]] = data[1];
            }
        }

        if (value[key]) {
            return value[key];
        } else {
            return '';
        }
    }
}

$(document).ready(function() {
    /* MMENU START */
    $('nav#menu').mmenu({
        extensions: ['fx-listitems-slide', 'fx-panels-zoom', 'fx-listitems-slide',
        'multiline', 'shadow-page', 'shadow-panels', 'listview-large', 'pagedim-black'],
        navbar: {title: 'Меню'}
    }, {
        language: "ru"
    });

    setTimeout(function () {
        $('#menu').css('display', '');
    }, 300);
    /* MMENU END */
});