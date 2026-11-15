<?php
/**
 * database/create_db.php
 * Script to create new database book_nest_fina and clone all tables, views, and data from ltweb_nhom12
 */

require_once __DIR__ . '/../inc/config.php';

$pdo = db();

echo "Creating databases 'book_nest_fina' and 'book_nest_final'...\n";
$pdo->exec("CREATE DATABASE IF NOT EXISTS book_nest_fina CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("CREATE DATABASE IF NOT EXISTS book_nest_final CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// Try mysqldump with empty password (standard XAMPP root)
$dumpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'booknest_clone_' . time() . '.sql';
$dumpCmd = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" -u root ltweb_nhom12 > \"$dumpFile\"";
exec($dumpCmd, $out, $ret);

if ($ret === 0 && file_exists($dumpFile) && filesize($dumpFile) > 1000) {
    echo "Cloning complete dump into book_nest_fina...\n";
    exec("\"C:\\xampp\\mysql\\bin\\mysql.exe\" -u root book_nest_fina < \"$dumpFile\"");
    echo "Cloning complete dump into book_nest_final...\n";
    exec("\"C:\\xampp\\mysql\\bin\\mysql.exe\" -u root book_nest_final < \"$dumpFile\"");
    @unlink($dumpFile);
    echo "Dump and import finished successfully!\n";
} else {
    echo "Using PDO to duplicate views and tables...\n";
    $views = $pdo->query("SHOW FULL TABLES FROM ltweb_nhom12 WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_NUM);
    foreach (['book_nest_fina', 'book_nest_final'] as $targetDb) {
        foreach ($views as $v) {
            $viewName = $v[0];
            $createViewSql = $pdo->query("SHOW CREATE VIEW ltweb_nhom12.`$viewName`")->fetch()['Create View'];
            // Replace db name
            $createViewSql = str_replace('`ltweb_nhom12`.', "`$targetDb`.", $createViewSql);
            $pdo->exec("USE `$targetDb`");
            $pdo->exec("DROP VIEW IF EXISTS `$targetDb`.`$viewName`");
            try {
                $pdo->exec($createViewSql);
            } catch (Throwable $e) {
                // Ignore if view creation error
            }
        }
    }
}

echo "Database verification for book_nest_fina:\n";
$tables = $pdo->query("SHOW TABLES FROM book_nest_fina")->fetchAll(PDO::FETCH_COLUMN);
echo "Total tables/views in book_nest_fina: " . count($tables) . "\n";
print_r($tables);
