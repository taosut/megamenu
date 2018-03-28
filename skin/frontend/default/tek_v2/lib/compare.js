var $j = jQuery.noConflict();
$j(document).ready(function () {
    bindAddToCart();
// Init the localStorage from the params
    $j('.cp-remove').on('click', function (e) {
        var productId = $j(this).attr('data-id');
        var params = $j('.params').value;
        swal({
                title: "Xóa khỏi danh sách ?",
                text: "Bạn có muốn xóa sản phẩm khỏi danh sách so sánh ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Xóa ',
                cancelButtonText: "Hủy bỏ",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function (isConfirm) {
                if (isConfirm) {
                    var compareList = JSON.parse(localStorage.getItem('compareList'));
                    var listItem = compareList.listItem;
                    var newList = [];
                    listItem.forEach(function (item) {
                        if (item.product_id != productId) {
                            newList.push(item);
                        }
                    });
                    var itemParams = [];
                    newList.forEach(function (item) {
                        itemParams.push({
                            'product_id': item.product_id
                        });
                    });
                    compareList.listItem = newList;
                    localStorage.setItem('compareList', JSON.stringify(compareList));
                    compareList.listItem = itemParams;
                    if (newList.length == 0) {
                        window.close();
                        return;
                    }
                    window.location.href = "/tek_v2/compare/index/compare?data=" + btoa(JSON.stringify(compareList));
                }
                else {
                    swal.close();
                }
            });
        e.stopPropagation();
        e.preventDefault();
    });
    function bindAddToCart() {
        $j('.wish-add-to-cart').bind('click', function (e) {
            var product_id = $j(this).attr('product-id');
            $j('.wish-add-to-cart').prop('disabled', true);
            $j('.ajax-loader-atc').show();
            $j('.loading').show();
//        var purchaseQty = parseInt($j('.qty-selector-input').val());
            /* Enhanced ecommerce add to cart*/
            /**
             * Measure adding a product to a shopping cart by using an 'add' actionFieldObject
             * and a list of productFieldObjects.
             */
            /* End Enhanced ecommerce add to cart*/

            var form = $j('#product_addtocart_form-' + product_id);
            var win = $j(window);
            var data = form.serializeArray();

//            console.log(data);

            $j.ajax({
                url: form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response) {
                    $j('.ajax-loader-atc').hide();
                    $j('.wish-add-to-cart').prop('disabled', false);
                    if (response.message !== '') {
                        $j('.add_to_cart_messages').empty();
                        if (response.error_flg == 0) {
                            swal({
                                title: "Thành công!",
                                text: "Đã thêm vào giỏ hàng!",
                                showConfirmButton: false,
                                type: "success"
                            });
                            setTimeout(function () {
                                swal.close();
                            }, 3000);
                        }
                        else {
                            if (response.message == 'Please specify the product\'s option(s).') {
                                response.message = 'Sản phẩm có chứa thuộc tính, vui lòng vào chi tiết sản phẩm';
                            }
                            $j('.add_to_cart_messages-' + product_id).append("<div class='alert alert-danger mb-5'>" + response.message + "</div>");
                            $j('.btn-tekshop').addClass('opa-1');
                        }
                    }

                    // Update Subtotal
                    $j('.cart-pr-subtotal').empty();
                    $j('.cart-pr-subtotal').html(addCommas(response.sub_total) + ' ₫');

                    // Update Grandtotal
                    $j('.cart-pr-grandtotal').empty();
                    $j('.cart-pr-grandtotal').html(addCommas(response.grand_total) + ' ₫');

                    // Update shopping cart body
                    $j('#shopping-cart-body').empty();
                    $j('#shopping-cart-body').append(response.ex_html);

                    // Update shopping cart header
                    $j('#shopping-cart-header').empty();
                    $j('#shopping-cart-header').append(response.ex_header_html);

                    $j('.wish-add-to-cart').prop('disabled', false);
                    $j('.loading').hide();
                }
            });
            e.preventDefault();
            e.stopPropagation();
        });
    }

    function addCommas(str) {
        var parts = (str + "").split("."),
            main = parts[0],
            len = main.length,
            output = "",
            first = main.charAt(0),
            i;

        if (first === '-') {
            main = main.slice(1);
            len = main.length;
        } else {
            first = "";
        }
        i = len - 1;
        while (i >= 0) {
            output = main.charAt(i) + output;
            if ((len - i) % 3 === 0 && i > 0) {
                output = "." + output;
            }
            --i;
        }
        // put sign back
        output = first + output;
        // put decimal part back
        if (parts.length > 1) {
            output += "." + parts[1];
        }
        return output;
    }

//* Keep scrollbar if description too long, comment for advance refactor
//    $j(window).scroll(function () {
//        if ($j(window).scrollTop() >= $j('.tekshop-cat-header').height() + $j('.tekshop-topbar').height() + $j('.overview-compare').height() + $j('.compare-wrapper').height()) {
//            if(!$j('.compare-fixed').hasClass('cp-fixed-show')){
//                $j('.compare-fixed').addClass('cp-fixed-show');
//                $j('.cp-helper-img').hide();
//                $j('.cp-helper-text').addClass('cp-helper-nameafter');
//                $j('.cp-name').hide();
//            }
//        }else{
//            var currentImage = $j('.cp-name').first();
//
//            if($j('.compare-fixed').hasClass('cp-fixed-show')){
//                $j('.compare-fixed').removeClass('cp-fixed-show');
//                $j('.cp-name').show();
//                $j('.cp-helper-img').show();
//                $j('.cp-helper-text').removeClass('cp-helper-nameafter');
//            }
//        }
//    });
});