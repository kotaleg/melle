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

document.addEventListener('DOMContentLoaded', () => {
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

    /* PRODUCT IMAGE START */
    if(jQuery().zoom) {
        if($(window).width() > 1200) {
            $(document).on('gallery.zoom.init', '.prod-card__item-big-photo', function () {
                var self = $(this);
                self.zoom();
            });

            if ($('.prod-card__item-big-photo').length > 0) {
                $('.prod-card__item-big-photo').trigger('gallery.zoom.init');
            }
        }
    }

    if(jQuery().slick) {
        $(".slider-for").slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            asNavFor: ".slider-nav"
        });

        $(".slider-nav").slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            asNavFor: ".slider-for",
            dots: false,
            focusOnSelect: true,
            vertical: true,
            adaptiveHeight: true,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        vertical: false,
                        arrows: false
                    }
                }
            ]
        });
    }

    /* PRODUCT IMAGE END */
});
