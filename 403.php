<?php
/**
 * 403.php - 403 Forbidden Error Page
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';

http_response_code(403);
$tieuDe = '403 - Access Denied';
$trang = '403';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <div style="text-align: center; padding: 60px 20px;">
    <h1 style="font-size: 4rem; color: #dc2626; margin-bottom: 10px;">403</h1>
    <h2>Access Denied (Forbidden)</h2>
    <p style="max-width: 600px; margin: 15px auto; color: #64748b; font-size: 1.1rem;">
      You do not have the required permissions to access this area. Administrative features and portal tools are restricted to authorized accounts.
    </p>
    <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
      <a href="index.php" class="nut nut--chinh">&larr; Back to Home</a>
      <a href="dang-nhap.php" class="nut nut--vien">Sign In with Another Account</a>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
