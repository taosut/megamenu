var $j = jQuery.noConflict();

$j(document).ready(function () {
    $j('.product-gift-icon').tooltip({
        container: 'body',
        html: true,
        trigger: 'hover'
    });

    // Slick
    $j('.list-top-banner').slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 300,
        nextArrow: '<i class="fa fa-chevron-right slider-next"></i>',
        prevArrow: '<i class="fa fa-chevron-left slider-prev"></i>',
        touchThreshold: 30
    });

    $j('.filter-section-item').each(function () {
        if (!$j(this).hasClass('active')) {
            $j(this).find('.filter-section-checkbox').prop('checked', false);
        }
    });

    $j('.bottom-pagination-block').html($j('.top-pagination-block').html());
    $j('.limiter-block').html($j('.hidden-limiter').html());

    $j('.product-gift-icon').click(function (e) {
        e.stopPropagation();
    });
});

// Click expand / collapse filter
function toggleFilterTitle(value) {
    var header = $j(value);
    var icon = $j(value).find('i');
    //getting the next element
    var content = header.next();
    //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
    content.slideToggle(300);
    //toggle icon
    icon.toggleClass('fa-caret-up fa-caret-down');
}
// End Click expand / collapse filter

//Show more cat
function showMoreCat() {
    var showmore = $j('#showmore-cat');
    var showmoreIcon = $j('#showmore-cat-icon');
    if (showmore.text().trim() == 'Xem thêm') {
        showmore.text("Thu gọn");
        showmoreIcon.toggleClass("fa-angle-down fa-angle-up");
    } else {
        showmore.text("Xem thêm");
        showmoreIcon.toggleClass("fa-angle-up fa-angle-down");
    }

    $j('.cattable-init-height').toggleClass("cattable-height-auto");
}
