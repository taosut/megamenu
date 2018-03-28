var $j = jQuery.noConflict();

$j(document).ready(function () {
    $j('.product-gift-icon').tooltip({
        container: 'body',
        html: true,
        trigger: 'hover'
    });

    /** Randomize slick slider **/
    $j.fn.randomize = function (selector) {
        var $elems = selector ? $j(this).find(selector) : $j(this).children(),
            $parents = $elems.parent();

        $parents.each(function () {
            $j(this).children(selector).sort(function (childA, childB) {
                // * Prevent last slide from being reordered
                if ($j(childB).index() !== $j(this).children(selector).length - 1) {
                    return Math.round(Math.random()) - 0.5;
                }
            }.bind(this)).detach().appendTo(this);
        });

        return this;
    };
    /** End Randomize slick slider **/

    // Slick
    $j('.promo-products').randomize().slick({
        infinite: false,
        arrows: false,
        dots: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 200,
        touchThreshold: 30
    });

    $j('.cat-products-content').each(function () {
        var count = $j(this).children().length;
        if (count > 2) {
            $j(this).randomize().slick({
                infinite: false,
                arrows: false,
                dots: true,
                slidesToShow: 2.5,
                slidesToScroll: 2,
                speed: 200,
                touchThreshold: 30
            });
        }
        else {
            $j(this).css('visibility', 'visible');
        }
    });

    if ($j('.recently-viewed-products').children().length > 2) {
        $j('.recently-viewed-products').slick({
            infinite: false,
            arrows: false,
            dots: true,
            slidesToShow: 2.5,
            slidesToScroll: 2,
            speed: 200,
            touchThreshold: 30
        });
    }
    else {
        $j('.recently-viewed-products').css('visibility', 'visible');
    }
    // End slick

    $j('.product-gift-icon').click(function (e) {
        e.stopPropagation();
    });

});
