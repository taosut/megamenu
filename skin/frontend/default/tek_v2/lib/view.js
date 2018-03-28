var $j = jQuery.noConflict();

$j(document).ready(function () {
    // Slick
    $j('.detail-gallery-images').slick({
        infinite: true,
        dots: false,
        slidesToShow: 4,
        slidesToScroll: 1,
        speed: 200,
        nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
        prevArrow: '<i class="fa fa-angle-left slider-prev"></i>'
    });

    if ($j('.included-products').children().length > 4) {
        $j('.included-products').slick({
            infinite: true,
            dots: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            speed: 200,
            nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
            prevArrow: '<i class="fa fa-angle-left slider-prev"></i>'
        });
    }
    else {
        $j('.included-products').css('visibility', 'visible');
    }

    if ($j('.related-products').children().length > 4) {
        $j('.related-products').slick({
            infinite: true,
            dots: false,
            slidesToShow: 4,
            slidesToScroll: 1,
            speed: 200,
            nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
            prevArrow: '<i class="fa fa-angle-left slider-prev"></i>'
        });
    }
    else {
        $j('.related-products').css('visibility', 'visible');
    }
    // End slick

    // Magnifier
    initThumbLens();

    // Click on gallery img item
    $j('.detail-gallery-img-item').click(function () {
        var largeImg = $j('#thumb');

        largeImg.attr('src', $j(this).data('src'));
        largeImg.attr('data-large-img-url', $j(this).data('large-img-url'));

        $j('#preview').find('img').remove();

        $j('#thumb-lens').remove();

        initThumbLens();
    });
    // End click on category menu btn
    // End magnifier

    // Change active tab
    $j('.product-tabs-block .nav-tabs li').click(function () {
        var index = $j(this).index();
        $j('.detail-header-scroll-tabs').children().removeClass('active').eq(index).addClass('active');
    });
    // End Change active tab

    // Click on detail header tab
    $j('.detail-header-scroll-tabs span').click(function () {
        var index = $j(this).index();
        $j('.product-tabs-block .nav-tabs').children().eq(index).find('span').trigger('click');

        $j('html, body').animate({scrollTop: $j('.product-tabs-block .nav-tabs').offset().top - 49}, 300);
    });
    // End click on detail header tab
});

// Init magnifier thumb lens
function initThumbLens() {
    if (!isTouchDevice() && $j('#thumb').length > 0) {
        var evt = new Event(),
            m = new Magnifier(evt);

        m.attach({thumb: '#thumb', zoom: 2});

        $j('#thumb-lens').hover(
            function () {
                $j('#preview').css('visibility', 'visible');
            },
            function () {
                $j('#preview').css('visibility', 'hidden');
            }
        );
    }
}
// End init magnifier thumb lens
