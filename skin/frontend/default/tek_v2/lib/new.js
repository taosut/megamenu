var $j = jQuery.noConflict();

$j(document).ready(function () {
    $j('.product-gift-icon').tooltip({
        container: 'body',
        html: true
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
        dots: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 300,
        nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
        prevArrow: '<i class="fa fa-angle-left slider-prev"></i>'
    });

    $j('.cat-products-content').each(function () {
        var count = $j(this).children().length;
        if (count > 4) {
            $j(this).randomize().slick({
                infinite: false,
                dots: true,
                slidesToShow: 4,
                slidesToScroll: 1,
                speed: 300,
                nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
                prevArrow: '<i class="fa fa-angle-left slider-prev"></i>'
            });
        }
        else {
            $j(this).css('visibility', 'visible');
        }
    });

    if ($j('.recently-viewed-products').children().length > 5) {
        $j('.recently-viewed-products').slick({
            infinite: false,
            dots: true,
            slidesToShow: 5,
            slidesToScroll: 1,
            speed: 300,
            nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
            prevArrow: '<i class="fa fa-angle-left slider-prev"></i>'
        });
    }
    else {
        $j('.recently-viewed-products').css('visibility', 'visible');
    }
    // End slick

    // Show product detail when hover (mouse position)
    onHoverProduct();
    // End show product detail when hover

    // Click on sub cat item
    $j('.sub-cats a').click(function () {
        var that = $j(this);

        var parentId = $j(this).data('parent-id');
        var catId = $j(this).data('cat-id');
        var catUrl = $j(this).data('cat-url');
        var getCatProductsUrl = $j(this).data('get-cat-products-url');

        var catProductsContent = $j('#cat-products-content-' + parentId);

        var catProductsAjaxLoader = $j('#cat-products-ajax-loader-' + parentId);
        var blockHeight = catProductsContent.height();
        catProductsAjaxLoader.css('height', blockHeight + 'px').css('line-height', (blockHeight - 20) + 'px');
        catProductsAjaxLoader.show();
        catProductsContent.slick('unslick').empty();

        $j.ajax({
            url: getCatProductsUrl,
            type: 'GET',
            data: {
                catId: catId
            },
            dataType: 'json',
            success: function (response) {
                catProductsAjaxLoader.hide();

                that.closest('.sub-cats').find('a').removeClass('active');
                that.addClass('active');

                var viewMoreLink = $j('#view-more-' + parentId);
                viewMoreLink.attr('href', catUrl);

                var catProducts = response.cat_products.trim();
                var catProductsCount = response.cat_products_count;

                if (catProductsCount > 0) {
                    catProductsContent.append(catProducts);
                    if (catProductsCount > 4) {
                        catProductsContent.randomize().slick({
                            infinite: true,
                            dots: true,
                            slidesToShow: 4,
                            slidesToScroll: 1,
                            speed: 300,
                            nextArrow: '<i class="fa fa-angle-right slider-next"></i>',
                            prevArrow: '<i class="fa fa-angle-left slider-prev"></i>'
                        });
                    }
                    else {
                        catProductsContent.css('visibility', 'visible');
                    }

                    onHoverProduct();

                    $j('.product-gift-icon').tooltip({
                        container: 'body',
                        html: true
                    });

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
                }
                else {
                    var noProducts = '<div class="sub-cats-no-products">Danh mục này hiện không có sản phẩm mới!</div>';
                    catProductsContent.append(noProducts);
                    $j('.sub-cats-no-products').css('height', blockHeight + 'px');
                }
                $j('.cat-products-content').css('visibility', 'visible');
            }
        });
    });
    // End Click on sub cat item
});

function onHoverProduct() {
    if (!isTouchDevice()) {
        var productDetail = $j('.product-detail');

        $j('.product-img').hover(
            function () {
                var productName = $j(this).data('product-name');
                var productPrice = $j(this).data('product-price');
                var productDesc = $j(this).data('product-desc');
                $j('.product-detail-header').empty().append(productName);
                $j('.product-detail-price').empty().append(productPrice);
                $j('.product-detail-description').empty().append(productDesc);
                productDetail.show();

            }, function () {
                var productDetail = $j('.product-detail');
                productDetail.hide();
            }
        );

        $j(window).mousemove(function (e) {
            var relX = e.pageX;
            var relY = e.pageY - $j(window).scrollTop();

            var positionVertical = (relY / $j(window).height() <= 0.6) ? 0 : (productDetail.height() + 5);
            var positionHorizontal = (relX / $j(window).width() <= 0.7) ? 0 : (productDetail.width() + 5);

            productDetail.css('top', (relY + 5 - positionVertical) + 'px');
            productDetail.css('left', (relX + 5 - positionHorizontal) + 'px');
        });
    }
}
