var $j = jQuery.noConflict();

$j(document).ready(function () {
    // Slick
    $j('.detail-gallery-images').slick({
        infinite: true,
        dots: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 200,
        autoplay: true,
        nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
        prevArrow: '<i class="fa fa-angle-left slider-prev"></i>',
        touchThreshold: 30
    });

    if ($j('.included-products').children().length > 2) {
        $j('.included-products').slick({
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
        $j('.included-products').css('visibility', 'visible');
    }

    if ($j('.related-products').children().length > 2) {
        $j('.related-products').slick({
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
        $j('.related-products').css('visibility', 'visible');
    }
    // End slick
});
