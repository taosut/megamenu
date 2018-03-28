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
    
    $j('.sub-cat-item-name').click(function(e) {
        e.stopPropagation();
    });

    $j('.header-cart').click(function () {
        $j('.header-cart').toggleClass('active');
    });

    // Hide when click outsite search-suggestion
    $j(window).click(function () {
        $j('.search-suggestion').addClass('hidden');
        $j('.header-cart').removeClass('active');
        $j('.mini-cart').removeClass('active');
        $j('.cat-menu').removeClass('cat-menu-scroll');
        $j('.search-input').css('border-bottom-left-radius', '3px');
    });

    $j(window).click(function () {
        $j('.search-suggestion').addClass('hidden');
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

    $j('.mini-cart').click(function (e) {
        e.stopPropagation();
    });
    // End Hide when click outsite search-suggestion

    // Click on search btn or view all
    $j('.search-btn').click(function () {
        var keySearch = $j('.search-input').val().trim();
        var searchOption = $j('.search-select').val();
        if (keySearch !== '') {
            var searchOption = $j('.search-select').val();
            var searchUrl = $j(this).data('search-url');
            var url = searchUrl + "?q=" + keySearch;
            if (searchOption != 0) {
                url += "&cat=" + searchOption;
            }
            window.location.href = url;
        }

    });
    // End click on search btn or view all

    // Search suggestion
    var timeoutSearch = null;

    $j('.search-input').keyup(function (e) {
        if (e.keyCode === 13) {
            var keySearch = $j(this).val().trim();
            var searchOption = $j('.search-select').val();
            if (keySearch !== '') {
                var searchOption = $j('.search-select').val();
                var searchUrl = $j(this).data('search-url');
                var url = searchUrl + "?q=" + keySearch;
                if (searchOption != 0) {
                    url += "&cat=" + searchOption;
                }
                window.location.href = url;
            }
        }
        else {
            var getSearchSuggestionUrl = $j(this).data('get-search-suggestion-url');
            if (timeoutSearch) {
                clearTimeout(timeoutSearch);
            }
            timeoutSearch = setTimeout(function () {
                searchProduct(getSearchSuggestionUrl);
            }, 500);
        }
    });
    // End search suggestion

    // Get breadcrumbs info
    var breadcrumbsHtml = $j('.breadcrumbs').html();
    if (window.location.href.indexOf("search/index/result") === -1) {
        $j('.tek-breadcrumb-content').append(breadcrumbsHtml);
    }
    $j('.breadcrumbs').remove();
    // End Get breadcrumbs info

    // Add to cart from list
    $j('.add-to-cart-from-list-btn').click(function () {
        var addToCartUrl = $j(this).data('add-to-cart-url');
        var productType = $j(this).data('product-type');
        var instockStatus = "Có";
        var productName = $j(this).data('product-name');
        var productId = $j(this).data('product-id');
        var productPrice = $j(this).data('product-price');
        if (instockStatus === 'Không') {
            localStorage.setItem("addToCartErrorMessage", "Sản phẩm này hiện đang tạm hết hàng!");
        }
        else {
            if (productType === 'simple') {
                /** Track facebook pixel AddToCart **/
                dataLayer.push({
                    'contentIds': [productId.toString()],
                    'value': productPrice
                });

                /** EE Track add to cart **/
                dataLayer.push({
                    'event': 'addToCart',
                    'ecommerce': {
                        'currencyCode': 'VND',
                        'add': {
                            'products': [{
                                'name': productName,
                                'id': productId,
                                'price': productPrice,
                                'quantity': 1
                            }]
                        }
                    }
                });
            } else if (productType === 'configurable') {
                localStorage.setItem("addToCartErrorMessage", "Vui lòng lựa chọn thuộc tính sản phẩm!");
            } else if (productType === 'bundle') {
                localStorage.setItem("addToCartErrorMessage", "Vui lòng kiểm tra kỹ thông tin sản phẩm!");
            }
        }
        $j('.add-to-cart-from-list-btn').prop('disabled', true);
        $j(this).find('.add-to-cart-from-list-icon').addClass('hidden');
        $j(this).find('.add-to-cart-from-list-ajax-loader').toggleClass('hidden display-inline');
        window.location.href = addToCartUrl;
    });
    // End Add to cart from list

    // Xu ly scroll
    var isProductPage = $j('.is-detail-page').val();
    var pointScrollTop = 0;
    // if (getIsHomePage() === '1') {
    //     pointScrollTop = $j('.cat-menu-item').last().offset().top - 100;
    // } else
    if (isProductPage && $j('.add-to-cart-btn').offset()) {
        pointScrollTop = $j('.add-to-cart-btn').offset().top - 80;
    } else {
        pointScrollTop = $j(window).width() / 3.2;
    }

    // Click on category menu btn
    $j('.category-btn').click(function (e) {
        e.stopPropagation();

        handleClickCategoryBtn(pointScrollTop);
    });
    // End click on category menu btn

    // Scroll over point scroll top
    var srtop = $j('.scroll-to-top');

    if ($j(window).scrollTop() > pointScrollTop) {
        srtop.css('right', '0');
        $j('.mini-cart').css('right', '0');

        // Collapse header
        collapseHeader(isProductPage);
    } else {
        // Expand header
        expandHeader(isProductPage);
    }

    // Check to see if the window is top if not then display button
    $j(window).scroll(function () {
        $j('.cat-menu').removeClass('cat-menu-scroll');

        if ($j(this).scrollTop() > pointScrollTop) {
            $j('.header-cart').removeClass('active');
            srtop.css('right', '0').css('transition', '0.3s right');
            $j('.mini-cart').css('right', '0').css('transition', '0.3s right');

            // Collapse header
            collapseHeader(isProductPage);

        } else {
            srtop.css('right', '-60px').css('transition', '0.3s right');
            $j('.mini-cart').css('right', '-60px').css('transition', '0.3s right').removeClass('active');
            changeSubCatMenuPosition();

            // Expand header
            expandHeader(isProductPage);
        }
    });

    // Click event to scroll to top
    srtop.click(function () {
        $j('html, body').animate({scrollTop: 0}, 800);
        return false;
    });
    // End xu ly scroll

    changeSubCatMenuPosition();
    checkMiniCartLength();
    getSearchSuggestionWidth();

    $j('.layout__wrapper').css('width', 1200 - ((1920 - $j(window).width()) / 1.5));

    // Responsive
    $j(window).resize(function () {
        changeSubCatMenuPosition();
        getSearchSuggestionWidth();
        pointScrollTop = (window.location.href.indexOf("detail") > -1) ? $j('.add-to-cart-btn').offset().top - 20 : ($j(window).width() / 3.2);

        var winWidth = $j(window).width();
        var changeWidth = 1920 - winWidth;
        $j('.layout__wrapper').css('width', 1200 - (changeWidth / 1.5));
    });
});

function searchProduct(getSearchSuggestionUrl) {
    var keySearch = $j('.search-input').val().trim();
    if (keySearch === '') {
        $j('.search-input').css('border-bottom-left-radius', '3px');
        $j('.search-suggestion').addClass('hidden');
        $j('.search-suggestion-results').empty();
    }
    else {
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
                }
            }
        });
    }
}

function handleClickCategoryBtn(pointScrollTop) {
    var catMenuWidth = $j('.cat-menu').width();
    $j('.cat-menu').toggleClass('cat-menu-scroll').css('width', catMenuWidth + 'px');
    if ($j(window).scrollTop() > pointScrollTop) {
        $j('.cat-menu').css('top', '140px');
    }
    else {
        $j('.cat-menu').css('top', '166px');
    }
    changeSubCatMenuPosition();
    // $j('.desktop-menu').css('display','block');
}

function changeSubCatMenuPosition() {
    if ($j('.cat-menu').hasClass('cat-menu-scroll')) {
        $j('.sub-cat-menu').css('left', ($j('.cat-menu').width()) + 'px');

        var timer;
        $j(".category-btn-block, .cat-menu-scroll").hover(function (e) {
            if (timer) timer = clearTimeout(timer)
        }).mouseleave(function (e) {
            timer = setTimeout(function () {
                $j('.cat-menu').removeClass('cat-menu-scroll');
            }, 1)
        });
    }
    else {
        $j('.sub-cat-menu').css('left', ($j('.cat-menu').width() + 16) + 'px');
    }
}

function checkMiniCartLength() {
    var miniCartLength = $j('.mini-cart-products').children().length;
    if (miniCartLength > 5) {
        $j('.mini-cart-item').css('border-right', 'none');
    }
}

function getSearchSuggestionWidth() {
    $j('.search-suggestion').css('width', ($j('.search-input').width() + 30) + 'px');
}

function expandHeader(isProductPage) {
    if (isProductPage) { // Neu la trang detail
        $j('.header').css('top', '0').css('transition', '0.3s ease-in-out');
        $j('.logo').css('opacity', '1').css('transition', '0.3s ease-in-out');
        $j('.search-bar').css('opacity', '1').css('transition', '0.3s ease-in-out');
        $j('.header-right-info').css('opacity', '1').css('transition', '0.3s ease-in-out');
        $j('.call-free').css('opacity', '1').css('transition', '0.3s ease-in-out');
        $j('.search-tags').css('opacity', '1').css('transition', '0.3s ease-in-out');
        $j('.detail-header-scroll-block').css('top', '-100px').css('transition', '0.3s ease-in-out');
        $j('.header-top-main-block').css('height', '100px');
        $j('.header-navigation').show();
    }
    else {
        $j('.header').css('top', '0').css('transition', '0.3s ease-in-out');
    }
}

function collapseHeader(isProductPage) {
    if (isProductPage) { // Neu la trang detail
        $j('.header').css('top', '-26px').css('transition', '0.3s ease-in-out');
        $j('.logo').css('opacity', '0').css('transition', '0.3s ease-in-out');
        $j('.search-bar').css('opacity', '0').css('transition', '0.3s ease-in-out');
        $j('.search-suggestion').addClass('hidden');
        $j('.call-free').css('opacity', '0').css('transition', '0.3s ease-in-out');
        $j('.search-tags').css('opacity', '0').css('transition', '0.3s ease-in-out');
        $j('.detail-header-scroll-block').css('top', '0').css('transition', '0.3s ease-in-out');
        $j('.header-top-main-block').css('height', '100px');
        $j('.header-navigation').hide();

        if ($j(window).scrollTop() > ($j('.product-tabs-block').offset().top - 50)) {
            $j('.detail-header-scroll-name').css('margin-top', '-25px').css('transition', '0.2s ease-in-out');
            $j('.detail-header-scroll-tabs').css('visibility', 'visible').css('bottom', '10px').css('transition', '0.2s ease-in-out');
        }
        else {
            $j('.detail-header-scroll-name').css('margin-top', '0').css('transition', '0.2s ease-in-out');
            $j('.detail-header-scroll-tabs').css('visibility', 'hidden').css('bottom', '-20px').css('transition', '0.2s ease-in-out');
        }
    }
    else {
        $j('.header').css('top', '-26px').css('transition', '0.3s ease-in-out');
    }
}

function showMiniCartDetail(value) {
    var cartQty = $j(value).data('cart-qty');
    if (cartQty > 0) {
        $j('.mini-cart').toggleClass('active');
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

