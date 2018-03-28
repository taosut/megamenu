<?php
//print_r($_SERVER["SERVER_NAME"]);
$host = "192.168.0.90";
$dbase = "phongvu";
$conn_name = 'zomart';
$password = 'DF3df#$E';

try {
    $conn = new PDO('mysql:host='.$host.';dbname='.$dbase, $conn_name, $password,  array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$GLOBALS['my_conn'] = $conn;
	$GLOBALS['my_dbase'] = $dbase;

} catch(PDOException $e) {
    echo "Cannot connect to database";
	print_r($e);
	exit;
}

?>