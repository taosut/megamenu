<?php
ini_set("date.timezone",'Asia/Ho_Chi_Minh');
error_reporting(E_ERROR);
ini_set("display_errors",0);


@define('APP_PATH', str_replace('\\', '/', dirname(__FILE__)));



define('CONF_ROOT', APP_PATH . '/_config/');
define('LIBRARY_ROOT', APP_PATH . '/_library/');
define('TPL_ROOT', APP_PATH . '/_tpl/');
define('RESOURCE_ROOT', APP_PATH . '/resources/');

$GLOBALS['key_code'] = "competitive";
$GLOBALS['combine_item_count'] = 3; 

$allow_ip = array(
    '175.98.145.6',
    '122.147.255.6',
    '175.98.145.7',
    '122.147.255.7',
    '175.98.145.8',
    '122.147.255.8',
    '175.98.145.18',
    '122.147.255.18',
    '175.98.145.19',
    '122.147.255.19',
    '175.98.145.20',
    '122.147.255.20',
    '175.98.145.21',
    '122.147.255.21',
    '175.98.145.22',
    '122.147.255.22',
    '113.190.244.226'
);

while(list($field,$value) = each($_POST))
{
    if(is_array($_POST[$field])){
        while(list($field2,$value2) = each($_POST[$field]))
        {
                if(is_array($_POST[$field][$field2])){
                    while(list($field3,$value3) = each($_POST[$field][$field2]))
                    {
                        $value3 = preg_replace('/(<script.*>)(.*)(<\/script>)/imxsU',"",$value3);
                        $_POST[$field][$field2][$field3]=addslashes(htmlspecialchars($value3));
                    }
                }
                else{
                    $value2 = preg_replace('/(<script.*>)(.*)(<\/script>)/imxsU',"",$value2);
                    $_POST[$field][$field2]=addslashes(htmlspecialchars($value2));
                }
        }
    }
    else{
        $value = preg_replace('/(<script.*>)(.*)(<\/script>)/imxsU',"",$value);
        $_POST[$field]=addslashes(htmlspecialchars($value));
    }
}
while(list($field,$value) = each($_GET))
{
        $value = preg_replace('/(<script.*>)(.*)(<\/script>)/imxsU',"",$value);
        $_GET[$field]=addslashes(htmlspecialchars($value));
}

require_once CONF_ROOT. "Db_conn.php";
require_once CONF_ROOT. "_function.php";
require_once CONF_ROOT. "config.php";

require_once LIBRARY_ROOT. "_decode.php";
require_once LIBRARY_ROOT. "project_control.php";
require_once LIBRARY_ROOT. "PHPMailerAutoload.php";


