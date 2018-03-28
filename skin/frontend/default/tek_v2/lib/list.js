var $j = jQuery.noConflict();

$j(document).ready(function () {
    $j('.product-gift-icon').tooltip({
        container: 'body',
        html: true
    });

    // Slick
    $j('.list-top-banner').slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 200,
        nextArrow: '<i class="fa fa-chevron-right slider-next"></i>',
        prevArrow: '<i class="fa fa-chevron-left slider-prev"></i>'
    });

    if ($j('.recently-viewed-products').children().length > 5) {
        $j('.recently-viewed-products').slick({
            infinite: true,
            dots: true,
            slidesToShow: 5,
            slidesToScroll: 1,
            speed: 200,
            nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
            prevArrow: '<i class="fa fa-angle-left slider-prev"></i>'
        });
    }
    else {
        $j('.recently-viewed-products').css('visibility', 'visible');
    }
    // End Slick

    $j('.filter-section-item').each(function () {
        if (!$j(this).hasClass('active')) {
            $j(this).find('.filter-section-checkbox').prop('checked', false);
        }
    });

    // Click switch list/grid view btn
    $j('.switch-list-view-btn').click(function () {
        $j('.switch-btn').removeClass('active');
        $j(this).addClass('active');
        $j('.grid-view').hide();
        $j('.list-view').show();
        localStorage.setItem('switchView', 'list');
    });

    $j('.switch-grid-view-btn').click(function () {
        $j('.switch-btn').removeClass('active');
        $j(this).addClass('active');
        $j('.list-view').hide();
        $j('.grid-view').show();
        localStorage.setItem('switchView', 'grid');
    });
    // End click switch list/grid view btn

    // Check switch view
    var switchView = localStorage.getItem('switchView');
    if (switchView) {
        if (switchView === 'grid') {
            $j('.switch-grid-view-btn').addClass('active');
            $j('.list-view').hide();
            $j('.grid-view').show();
        }
        else {
            $j('.switch-list-view-btn').addClass('active');
            $j('.grid-view').hide();
            $j('.list-view').show();
        }
    }
    else {
        $j('.switch-grid-view-btn').addClass('active');
        $j('.list-view').hide();
        $j('.grid-view').show();
    }
    // End check switch view

    $j('.bottom-pagination-block').html($j('.top-pagination-block').html());
    $j('.limiter-block').html($j('.hidden-limiter').html());
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
