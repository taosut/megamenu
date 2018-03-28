var $j = jQuery.noConflict();

$j(document).ready(function () {
    /** Overlay multiple modals backdrop **/
    $j(document).on('show.bs.modal', '.modal', function (event) {
        var zIndex = 1040 + (10 * $j('.modal:visible').length);
        $j(this).css('z-index', zIndex);
        setTimeout(function() {
            $j('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    /** End Overlay multiple modals backdrop **/

    $j('.header-cart').click(function () {
        $j('.header-cart').toggleClass('active');
    });

    // Hide when click outsite component
    $j(window).click(function () {
        $j('.search-suggestion').addClass('hidden');
        $j('.header-cart').removeClass('active');
        $j('.search-input').css('border-bottom-left-radius', '3px');
    });

    $j('.search-input').click(function (e) {
        e.stopPropagation();
    });

    $j('.search-suggestion').click(function (e) {
        e.stopPropagation();
    });

    $j('.header-cart').click(function (e) {
        e.stopPropagation();
    });
    // End Hide when click outsite search-suggestion

    // Click on search btn or view all
    $j('.search-btn').click(function () {
        var keySearch = $j('.search-input').val().trim();
        if (keySearch !== '') {
            var searchUrl = $j(this).data('search-url');
            window.location.href = searchUrl + "?q=" + keySearch;
        }
    });
    // End click on search btn or view all

    // Search suggestion
    var timeoutSearch = null;

    $j('.search-input').keyup(function (e) {
        if (e.keyCode === 13) {
            var keySearch = $j(this).val().trim();
            if (keySearch !== '') {
                var searchUrl = $j(this).data('search-url');
                window.location.href = searchUrl + "?q=" + keySearch;
            }
        }
        else {
            var getSearchSuggestionUrl = $j(this).data('get-search-suggestion-url');
            if (timeoutSearch) {
                clearTimeout(timeoutSearch);
            }
            timeoutSearch = setTimeout(function () {
                searchProduct(getSearchSuggestionUrl);
            }, 1000);
        }
    });
    // End search suggestion

    // Xu ly scroll
    var pointScrollTop = $j(window).width() / 3.2;

    // Scroll over point scroll top
    var srtop = $j('.scroll-to-top');

    if ($j(window).scrollTop() > pointScrollTop) {
        srtop.css('right', '0');
    }

    // Check to see if the window is top if not then display button
    $j(window).scroll(function () {
        if ($j(this).scrollTop() > pointScrollTop) {
            $j('.header-cart').removeClass('active');
            srtop.css('right', '0').css('transition', '0.3s right');
        } else {
            srtop.css('right', '-60px').css('transition', '0.3s right');
        }
    });

    // Click event to scroll to top
    srtop.click(function () {
        $j('html, body').animate({scrollTop: 0}, 800);
        return false;
    });
    // End xu ly scroll

    // Responsive
    $j(window).resize(function () {
        pointScrollTop = $j(window).width() / 3.2;
    });
});

function searchProduct(getSearchSuggestionUrl) {
    var keySearch = $j('.search-input').val().trim();
    if (keySearch === '') {
        $j('.search-suggestion').addClass('hidden');
        $j('.search-suggestion-results').empty();
        $j('.search-tags').removeClass('hidden');
    }
    else {
        $j('.search-ajax-loader').removeClass('hidden');
        $j.ajax({
            url: getSearchSuggestionUrl,
            type: 'GET',
            data: {
                q: keySearch
            },
            dataType: 'json',
            success: function (response) {
                $j('.search-input').css('border-bottom-left-radius', '0');

                var result_suggestion = response.result_suggestion.trim();
                var result_suggestion_count = response.result_suggestion_count;

                $j('.search-suggestion-not-found').addClass('hidden');

                // Update search result
                $j('.search-suggestion').removeClass('hidden');

                $j('.search-suggestion-results').empty();

                if (result_suggestion_count > 0) { // Tim thay ket qua
                    $j('.search-suggestion-results').append(result_suggestion);
                    $j('.search-ajax-loader').addClass('hidden');
                    $j('.search-tags').addClass('hidden');
                    $j('.search-suggestion-view-all').click(function () {
                        var keySearch = $j('.search-input').val().trim();
                        if (keySearch !== '') {
                            var searchUrl = $j(this).data('search-url');
                            window.location.href = searchUrl + "?q=" + keySearch;
                        }
                    });
                }
                else { // Khong tim thay ket qua
                    $j('.search-suggestion-not-found').removeClass('hidden');
                    $j('.search-ajax-loader').addClass('hidden');
                    $j('.search-tags').addClass('hidden');
                }
            }
        });
    }
}

function isTouchDevice() {
    return (('ontouchstart' in window)
    || (navigator.MaxTouchPoints > 0)
    || (navigator.msMaxTouchPoints > 0));
}

function goBack() {
    $j('.go-back-btn').prop('disabled', true);
    $j('.go-back-ajax-loader').removeClass('hidden');
    window.history.back();
}
