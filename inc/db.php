<?php
/**
 * inc/db.php - Kết nối cơ sở dữ liệu MySQL qua PDO (Singleton pattern)
 * Nhóm 12 - Lớp 24CNTT3 - Trường ĐH Sư phạm - ĐH Đà Nẵng
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = '127.0.0.1';
        $dbname = 'ltweb_nhom12';
        $user = 'root';
        $pass = '';
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
    }
    return $pdo;
}
