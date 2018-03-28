<?php
/**
 * @author hoang.pt *
 * @package Phongvu_Fault
 */

try {
    $this->startSetup();

    $this->run("
        CREATE TABLE `pv_fault_attempt`  (
          `attempt_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `date` datetime(0) NOT NULL,
          `user` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
          `client_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
          PRIMARY KEY (`attempt_id`) USING BTREE
        ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;
        
        CREATE TABLE `pv_fault_error`  (
          `error_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `date` datetime(0) NOT NULL,
          `type` tinyint(1) NOT NULL,
          `error` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
          PRIMARY KEY (`error_id`) USING BTREE
        ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;
        
        CREATE TABLE `pv_fault_log`  (
          `log_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
          `store_id` smallint(5) NOT NULL,
          `date` datetime(0) NOT NULL,
          `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
          `request_path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
          `referer` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
          `client_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
          PRIMARY KEY (`log_id`) USING BTREE
        ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;
    ");

    $this->endSetup();
} catch (Exception $e) {
    echo "<pre>";
    print_r($e);
    die;
}