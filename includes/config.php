<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'auth_system');



try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("حدث خطأ في قاعدة البيانات: " . $e->getMessage());
}
function isPrinterOnline($ip, $port = 161, $timeout = 1) {
    $conn = @fsockopen($ip, $port, $errno, $errstr, $timeout);
    if ($conn) {
        fclose($conn);
        return true;
    }
    return false;
}
// إعدادات الجلسة
session_start();
?>