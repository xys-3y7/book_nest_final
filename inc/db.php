<?php
/**
 * inc/db.php - Kết nối cơ sở dữ liệu MySQL qua PDO đọc cấu hình từ tệp .env
 * Nhóm 12 - Lớp 24CNTT3 - Chương 7: Bảo mật ứng dụng web
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $dbname = $_ENV['DB_DATABASE'] ?? 'book_nest_final';
        $user   = $_ENV['DB_USERNAME'] ?? 'root';
        $pass   = $_ENV['DB_PASSWORD'] ?? '';
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Tương thích linh hoạt giữa môi trường XAMPP mặc định (mật khẩu rỗng) và môi trường có đặt mật khẩu 123456
            if ($pass !== '') {
                try {
                    $pdo = new PDO($dsn, $user, '', $options);
                } catch (PDOException) {
                    throw $e;
                }
            } else {
                throw $e;
            }
        }
    }
    return $pdo;
}
