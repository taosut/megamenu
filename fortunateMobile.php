<?php
require_once "app/Mage.php";
umask(0);
Mage::app();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Vòng quay Phong Vũ</title>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <link rel="stylesheet" type="text/css" href="/skin/frontend/default/tekshop/css/bootstrap.min.css" media="all"/>
        <link rel="stylesheet" type="text/css" href="/skin/frontend/default/tekshop/css/styles.css" media="all"/>
        <link rel="stylesheet" type="text/css" href="/skin/frontend/default/tekshop/css/custom.css" media="all"/>
        <link rel="stylesheet" type="text/css" href="/skin/frontend/default/tekshop/css/custom.v2.css" media="all"/>
        <link rel="stylesheet" type="text/css" href="/skin/frontend/default/tekshop/css/font-awesome.min.css" media="all"/>
        <script type="text/javascript" src="/skin/frontend/default/tekshop/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="/skin/frontend/default/tekshop/js/jquery-ui.js" defer></script>
        <script type="text/javascript" src="/skin/frontend/default/tekshop/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/skin/frontend/default/tekshop/js/chosen.jquery.min.js"></script>
        <script type="text/javascript" src="/skin/frontend/default/tekshop/js/Winwheel.js"></script>
        <script type="text/javascript" src="/skin/frontend/default/tekshop/js/TweenMax.min.js"></script>
        <script type="text/javascript" src="/skin/frontend/default/tekshop/js/sweetalert.min.js" defer></script>
    </head>
    <body>
        <div style="position: relative">
            <div class="close-wheel" data-dismiss="modal">✕</div>
            <img class="mileStone" src="/skin/frontend/default/tekshop/images/mileStone.png"/>
            <canvas id='canvas' width='100%' height='400'>
                Trình duyệt hiện tại của bạn không hỗ trợ Vòng Quay Phong Vũ
            </canvas>
            <div class="spin-button-wrapper">
                <button class="btn-spin">Bấm để quay </button>
            </div>
        </div>
        <div id="fortunateResultModal" class="modal fade" role="dialog">
            <div class="fortunateDialogWrapper">
                <div class="fortunateDialog">
                    <div class="fortunateModalClose" data-dismiss="modal">✕</div>
                    <div class="fortunateWin" style="display: none">
                        <div class="fortunateTop">
                            <span class="mess-gift">Chúc mừng bạn đã trúng Voucher trị giá </span><span class="FWgift"></span>
                        </div>
                        <div class="fortunateBottom">
                            Mã giảm giá: <span class="FWcoupon"></span>
                        </div>
                    </div>
                    <div class="fortunateLose" style="display: none">
                        Chúc bạn may mắn lần sau !!
                    </div>
                </div>
            </div>
        </div>
        <script>
            $j = jQuery.noConflict();
            var countAlert = 0;
            var coupon = "";
            var gift = "";
            <?php $session = Mage::getSingleton('customer/session'); ?>
            $j('.fortunate-wheel-event').on('click', function (e) {
                var isLogin = false;
                <?php if ($session->isLoggedIn()):?>
                isLogin = true;
                <?php endif;?>
                if (isLogin) {
                    $j('#fortunateHint').modal();
                } else {
                    $j('#loginModal').modal();
                    var message = "Vui lòng đăng nhập để quay thưởng!";
                    $j('#error-login').html(message);
                }
            });

            $j('.confirm-hint').on('click',function(e){
                $j('#fortunateHint').modal('toggle');
                $j('#fortunateModal').modal();
                $j.ajax({
                    url: "/igame/wheel/index/isRoll/",
                    data: {'customer_id': '<?php echo $session->getCustomer()->getId()?>'},
                    type: 'GET',
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data.status == 'is_roll') {
                            if (data.data.rule_id == 0) {
                                gift = "do_den";
                            } else {
                                gift = addCommas(data.data.rule_name.replace("VQPV_", ""));
                                coupon = data.data.coupon;
                            }
                            var mess = "Bạn đã quay thưởng trúng voucher trị giá ";
                            appendPrice(mess, gift, coupon);
                        }
                        else {
                            $j('.btn-spin').removeClass('hidden');
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("Status: " + textStatus);
                        console.log("Error: " + errorThrown);
                    }
                });
            });

            $j('#canvas').attr('width', $j(window).width());
            var theWheel = new Winwheel({
                'canvasID': 'myCanvas',
                'numSegments': 24,
                'rotationAngle': -34,
                'drawMode': 'image',
                'segments':
                    [
                        {'text': '1'},
                        {'text': '2'},
                        {'text': '3'},
                        {'text': '4'},
                        {'text': '5'},
                        {'text': '6'},
                        {'text': '7'},
                        {'text': '8'},
                        {'text': '9'},
                        {'text': '10'},
                        {'text': '11'},
                        {'text': '12'},
                        {'text': '13'},
                        {'text': '14'},
                        {'text': '15'},
                        {'text': '16'},
                        {'text': '17'},
                        {'text': '18'},
                        {'text': '19'},
                        {'text': '20'},
                        {'text': '21'},
                        {'text': '22'},
                        {'text': '23'},
                        {'text': '24'}
                    ],
                'pointerAngle': 270,
                'lineWidth': 1,
                'outerRadius': 160,
                'innerRadius': 50,
                'lineWidth': 3,
                'pointerGuide':
                    {
                        'display': true,
                        'strokeStyle': 'red',
                        'lineWidth': 3
                    },
                'animation':
                    {
                        'type': 'spinToStop',
                        'duration': 12,
                        'spins': 6,
                        'callbackFinished': 'alertPrize()',
                        'easing': 'Power4.easeOut'
                    }
            });
            loadedImg = new Image();
            loadedImg.onload = function () {
                theWheel.wheelImage = loadedImg;
                theWheel.draw();
            };

            loadedImg.src = "/skin/frontend/default/tekshop/images/Wheel.png";

            $j('.btn-spin').on('click', function (e) {
                // $j('.btn-spin').attr('disabled', 'disabled');
                $j('.btn-spin').addClass('hidden');
                theWheel.startAnimation();
                $j.ajax({
                    url: "/igame/wheel",
                    data: {'customer_id': '<?php echo $session->getCustomer()->getId()?>'},
                    type: 'POST',
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data.status == 'processing') {
                            swal("Hệ thống đang xử lý, vui lòng đợi trong giây lát!");
                        }
                        else if (data.status) {
                            gift = data.data.rule_name;
                            var min = 0;
                            var max = 0;
                            switch (gift) {
                                case 'VQPV_100000':
                                    min = 1;
                                    max = 22;
                                    break;
                                case 'VQPV_200000':
                                    min = 23;
                                    max = 67;
                                    break;
                                case 'VQPV_300000':
                                    min = 68;
                                    max = 112;
                                    break;
                                case 'VQPV_500000':
                                    min = 158;
                                    max = 203;
                                    break;
                                case 'VQPV_2000000':
                                    min = 293;
                                    max = 337;
                                    break;
                                case 'VQPV_20000':
                                    min = 203;
                                    max = 247;
                                    break;
                                case 'VQPV_50000':
                                    min = 113;
                                    max = 157;
                                    break;
                                default:
                                    min = 248;
                                    max = 292;
                            }
                            var stopAt = Math.random() * (max - min) + min + 22.5;
                            if (data.data.rule_id == 0) {
                                gift = "do_den";
                            } else {
                                gift = addCommas(gift.replace("VQPV_", ""));
                            }
                            coupon = data.data.coupon;
                            theWheel.stopAnimation();
                            theWheel.rotationAngle = -22.5;
                            theWheel.draw();
                            theWheel.animation.stopAngle = stopAt;
                            theWheel.startAnimation();
                        } else {
                            swal("Có lỗi xảy ra, vui lòng thử lại !");
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("Status: " + textStatus);
                        console.log("Error: " + errorThrown);
                    }
                });

                e.stopPropagation();
                e.preventDefault();
            });

            $j('.resetAnimation').on('click', function (e) {
                $j('.btn-spin').removeAttr('disabled');
                theWheel.stopAnimation();
                theWheel.rotationAngle = -22.5;
                theWheel.draw();
                e.stopPropagation();
                e.preventDefault();
            });

            function alertPrize() {
                countAlert++;
                if (countAlert == 2) {
                    var mess = "";
                    appendPrice(mess, gift, coupon);
                }
            }

            function appendPrice(_mess, _gift, _coupon) {
                if (_gift == "do_den") {
                    $j('.fortunateLose').show();
                    $j('#fortunateModal').modal('toggle');
                    $j('#fortunateResultModal').modal();
                    $j('.btn-spin').removeAttr('disabled');
                } else {
                    _gift = addCommas(_gift.replace("VQPV_", ""));
                    if (_mess !== "") {
                        $j('.mess-gift').html(_mess);
                    }
                    $j('#fortunateModal').modal('toggle');
                    $j('.FWgift').html(_gift);
                    $j('.FWcoupon').html(_coupon);
                    $j('.fortunateWin').show();
                    $j('#fortunateResultModal').modal();
                    $j('.btn-spin').removeAttr('disabled');
                }
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
        </script>
    </body>
</html>