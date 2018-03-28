<?php
require_once "app/Mage.php";
umask(0);
Mage::app();
Mage::getSingleton('core/session', array('name' => 'adminhtml'));

$type = $_GET['type'];
if ($type) {
    $email = $_GET['email'];
    $username = $_GET['username'];
    $password = $_GET['password'];
    $accessToken = $_GET['accessToken'];
    $user = Mage::getModel('admin/user')->getCollection();
    $user = $user->addFieldToFilter('email', $email)->getData();
    if (!count($user)) {
        try {
            $userModel = Mage::getModel('admin/user');
            if ($userModel->authenticate($username, $password)) {
                $editUser = Mage::getModel('admin/user')
                    ->getCollection()
                    ->addFieldToFilter('username', $username)
                    ->getData();
                if (!strstr($editUser['email'], "@teko.vn")) {
                    $userModel->load($editUser[0]['user_id']);
                    $userModel->setData('email', $email);
                    try {
                        $userModel->save();
                        echo(json_encode(array(
                                'message' => "success"
                                //'redirect' => "/index.php/admin/admin/dashboard/"
                            )
                        ));
                        return;
                    } catch (Exception $e) {
                        $result = array('error' => "Cập nhật email có vấn đề !");
                        echo(json_encode($result));
                        return;
                    }
                } else {
                    $result = array('error' => "Tài khoản đã được mapping");
                    echo(json_encode($result));
                    return;
                }

            } else {
                $result = array('error' => "Tài khoản/mật khẩu không chính xác");
                echo(json_encode($result));
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => "Đăng nhập thất bại");
            echo(json_encode($result));
            return;
        }
    } else {
        $result = array('error' => "Email đã được sử dụng để mapping tài khoản");
        echo(json_encode($result));
        return;
    }
}
$accessToken = $_GET['accessToken'];
$apiUrlSso = (string)Mage::getConfig()->getNode('global/sso_url') . 'validate_access_token';
$client = new Varien_Http_Client($apiUrlSso);
$client->setMethod(Varien_Http_Client::GET);
$client->setParameterGet('accessToken', $accessToken);
try {
    $response = $client->request();
    if ($response->isSuccessful()) {
        $responseData = json_decode($response->getBody(), true);
        $email = $responseData['email'];
        $user = Mage::getModel('admin/user')->load($responseData['id'], 'sso_id');
        if (!$user->getId())
            $user = $user->load($responseData['email'], 'email');
        if ($user->getId()) {
            if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                Mage::getSingleton('adminhtml/url')->renewSecretUrls();
            }
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->beginTransaction();
            if ($user->getEmail() != $responseData['email'] || $user->getSsoId() != $responseData['id']) {
                $user->addData(array(
                    'email' => $responseData['email'],
                    'sso_id' => $responseData['id']
                ))->save();
            }
            $connection->commit();
            $session = Mage::getSingleton('admin/session');
            $session->setIsFirstVisit(true);
            $session->setUser($user);
            $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
            $_SESSION["ext_id"] = $responseData['id'];
            Mage::dispatchEvent('admin_session_user_login_success                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     -', array('user' => $user));
            if ($session->isLoggedIn()) {
                header('Location: ' . '/index.php/admin/dashboard/');
            } else {
                $message = ("Có vấn đề khi đăng nhập !");
                var_dump($message);
                die;
            }
        } else {
            $firstName = explode(' ', $responseData['name'])[0];
            $lastName = substr($responseData['name'], strlen($firstName) + 1);
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->beginTransaction();
            $roleColletion = Mage::getModel("admin/roles")->getCollection()
                ->addFieldToFilter('role_name', ['eq' => 'New_User'])
                ->getFirstItem();
            if (!$roleColletion) {
                $role = Mage::getModel("admin/roles")
                    ->setName('New_User')
                    ->setRoleType('G')
                    ->save();
            } else {
                $role = Mage::getModel("admin/roles")->load($roleColletion->getId());
            }
            $newUser = Mage::getModel('admin/user')
                ->setData(array(
                    'username' => $email,
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                    'email' => $email,
                    'password' => 'abcd1234',
                    'is_active' => 1,
                    'asia_id' => $responseData['asia_id'],
                    'sso_id' => $responseData['id']
                ))->save();
            $newUser->setRoleIds(array($role->getId()))
                ->setRoleUserId($newUser->getUserId())
                ->saveRelations();
            $connection->commit();
            $session = Mage::getSingleton('admin/session');
            $session->setIsFirstVisit(true);
            $session->setUser($newUser);
            $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
            $_SESSION["ext_id"] = $responseData['id'];
            Mage::dispatchEvent('admin_session_user_login_success', array('user' => $newUser));
            if ($session->isLoggedIn()) {
                header('Location: ' . '/index.php/admin/admin/dashboard/');
            } else {
                $message = ("Có vấn đề khi đăng nhập !");
                var_dump($message);
                die;
            }
        }
        $accessToken = $_GET['accessToken'];
        $apiUrlSso = (string)Mage::getConfig()->getNode('global/sso_url') . 'validate_access_token';
        $client = new Varien_Http_Client($apiUrlSso);
        $client->setMethod(Varien_Http_Client::GET);
        $client->setParameterGet('accessToken', $accessToken);
        try {
            $response = $client->request();
            if ($response->isSuccessful()) {
                $response = json_decode($response->getBody());
                $tokenUser = $response;
            } else {
                $message = "Có lỗi xảy ra, xin vui lòng thử lại sau !";
                var_dump($message);
                die;
            }
        } catch (Exception $e) {
            var_dump($e->getTraceAsString());
            die;
        }
    } else {
        $message = ("Access token không hợp lệ !");
        var_dump($message);
        die;
    }
} catch (Exception $e) {
    $connection->rollback();
    $message = ($e->getTraceAsString());
    var_dump($message);
    die;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>403</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="60"/>
    <link rel="stylesheet" type="text/css"
          href="/skin/frontend/default/tekshop/css/bootstrap.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="/skin/frontend/default/tekshop/css/styles.css"
          media="all"/>
    <link rel="stylesheet" type="text/css" href="/skin/frontend/default/tekshop/css/custom.css"
          media="all"/>
    <link rel="stylesheet" type="text/css" href="/skin/frontend/default/tekshop/css/custom.v2.css"
          media="all"/>
    <link rel="stylesheet" type="text/css"
          href="/skin/frontend/default/tekshop/css/font-awesome.min.css" media="all"/>
    <link rel="stylesheet" type="text/css"
          href="http://tekshop.local/skin/frontend/default/tekshop/css/sweetalert.css"/>

    <script type="text/javascript"
            src="/skin/frontend/default/tekshop/js/jquery-3.1.0.min.js"></script>
    <script type="text/javascript" src="/skin/frontend/default/tekshop/js/jquery-ui.js"
            defer></script>
    <script type="text/javascript"
            src="/skin/frontend/default/tekshop/js/bootstrap.min.js"></script>
    <script type="text/javascript"
            src="/skin/frontend/default/tekshop/js/chosen.jquery.min.js"></script>

    <script type="text/javascript" src="/skin/frontend/default/tekshop/js/sweetalert.min.js" defer></script>
    <style>
        .container403 {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="container403">
    <div class="show-403" style="text-align: center;">
        <img src="/skin/frontend/default/tekshop/images/403.png">
        <div style="margin-top: 10px;">
            <h1 style="color:#566781"> Bạn không có quyền truy cập khu vực này !</h1>
            <?php if ($accessToken):
                echo "<h2 style=\"color:#566781\"> Nếu bạn đã có tài khoản Magento Admin, vui lòng xác thực <span id=\"auth-open-form\" style=\"cursor:pointer;text-decoration: underline\">tại đây</span></h2>";
            endif; ?>
            <h2> Liên hệ: <a href="https://teko.facebook.com/profile.php?id=100021563803003/" target="_blank">Nguyễn
                    Quang Trung </a> hoặc <a href="https://teko.facebook.com/profile.php?id=100016645097404"
                                             target="_blank">Phan Tích Hoàng </a></h2>
        </div>
    </div>
    <div class="auth-confirm-form" style="display: none">
        <div class="row row-style-2" style="min-height: 730px;">
            <div class="col-lg-6 col-md-12 cart-col-1">
                <div class="panel panel-default address-list payment-address-block">
                    <div class="panel-body pb-0">
                        <h3 style="color:#566781">Xác thực tài khoản</h3>
                        <div class="panel-body">
                            <form class="form-horizontal" role="form" id="address-info">
                                <div class="form-group">
                                    <div style="color:#566781;text-align: center">
                                        <p>Vui lòng kiểm tra thông tin cá nhân và xác thực tài khoản Magento Admin:</p>
                                    </div>
                                    <div class="text-center message-auth" style="color:red;text-align: center"></div>
                                </div>
                                <div class="form-group row" style="margin-top: 52px;">
                                    <label for="user_name" class="col-lg-4 control-label visible-lg-block label-input">Tài
                                        khoản</label>
                                    <div class="col-lg-8">
                                        <input type="hidden" name="current_email" id="current_email"
                                               value="<?php echo $tokenUser->email ?>">
                                        <input type="hidden" name="access-token" id="access-token"
                                               value="<?php echo $accessToken ?>">
                                        <input type="text" name="user_name" class="form-control address" id="user_name"
                                               placeholder="Nhập tài khoản" required="">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-lg-4 control-label visible-lg-block label-input">Mật
                                        khẩu</label>
                                    <div class="col-lg-8">
                                        <input type="password" name="password" class="form-control address"
                                               id="password" placeholder="Nhập mật khẩu" required="">
                                    </div>
                                </div>
                                <div class="bottom-static-mobile">
                                    <div class="form-group row end">
                                        <div class="col-lg-8 col-lg-offset-4">
                                            <input type="hidden" name="address_id" value="">
                                            <button id="btn-auth" type="button" class="btn btn-primary btn-custom3"
                                                    style="padding: 15px 0;color: white !important;font-size: 14px !important;text-transform: uppercase !important;width: 100% !important;"
                                                    value="create">
                                                Xác thực
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 cart-col-2">
                <div class="panel panel-default address-list payment-address-block">
                    <div class="panel-body pb-0">
                        <h3 style="color:#566781">Thông tin cá nhân</h3>
                        <div class="panel-body">
                            <form class="form-horizontal" role="form" id="address-info">
                                <div class="form-group row">
                                    <label for="id" class="col-lg-4 control-label visible-lg-block label-input">Mã nhân
                                        viên</label>
                                    <div class="col-lg-8">
                                        <input type="number" name="id" class="form-control address" id="id"
                                               value="<?php echo $tokenUser->id; ?>" disabled>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="full_name" class="col-lg-4 control-label visible-lg-block label-input">Họ
                                        tên</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="full_name" class="form-control address" id="full_name"
                                               value="<?php echo $tokenUser->name; ?>" disabled>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="role" class="col-lg-4 control-label visible-lg-block label-input">Vị
                                        trí</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="role" class="form-control address" id="role"
                                               value="<?php echo $tokenUser->title; ?>" disabled>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="telephone" class="col-lg-4 control-label visible-lg-block label-input">Điện
                                        thoại di
                                        động</label>
                                    <div class="col-lg-8">
                                        <input type="number" name="telephone" class="form-control address"
                                               id="telephone" value="<?php echo $tokenUser->phone; ?>" disabled>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="email"
                                           class="col-lg-4 control-label visible-lg-block label-input">Email</label>
                                    <div class="col-lg-8">
                                        <input type="email" name="email" class="form-control address" id="email"
                                               value="<?php echo $tokenUser->email; ?>" disabled>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="text-center">
                        <h2> Liên hệ: <a href="https://teko.facebook.com/profile.php?id=100021563803003/"
                                         target="_blank">Nguyễn Quang Trung </a> hoặc <a
                                    href="https://teko.facebook.com/profile.php?id=100016645097404" target="_blank">Phan
                                Tích Hoàng </a></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade bs-example-modal-sm" id="myPleaseWait" tabindex="-1"
         role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                    <span class="glyphicon glyphicon-time">
                    </span> &nbsp;Vui lòng chờ trong giây lát !
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-info
                    progress-bar-striped active"
                             style="width: 100%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var $j = jQuery.noConflict();
    $j('#auth-open-form').on('click', function (e) {
        $j('.show-403').hide(300);
        $j('.auth-confirm-form').show(300);
        e.stopPropagation();
        e.preventDefault();
    });

    $j('#btn-auth').on('click', function (e) {
        var username = $j('#user_name').val();
        var password = $j('#password').val();
        var currentEmail = $j('#current_email').val();
        var accessToken = $j('#access-token').val();
        $j("#myPleaseWait").modal();
        $j.ajax({
            url: '/sso.php',
            data: {
                username: username,
                password: password,
                email: currentEmail,
                accessToken: accessToken,
                type: 1
            },
            dataType: 'json',
            success: function (data) {
                if (data.message === "success") {
                    $j('#myPleaseWait').modal('hide');
                    swal({
                        title: "Thành công",
                        type: "success",
                        showConfirmButton: false,
                        text: "Mapping tài khoản thành công ! Vui lòng chờ trong giây lát hoặc reload lại trang."
                    });
                    location.reload();
                    //window.location.href="/sso.php?accessToken=" + accessToken ;

                }
                if (data.error) {
                    $j('#myPleaseWait').modal('hide');
                    $j('.message-auth').html(data['error']);
                    return;
                }
            }
        });
        e.preventDefault();
        e.stopPropagation();
    });
</script>

</body>


