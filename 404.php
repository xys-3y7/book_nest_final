<?php
/**
 * 404.php - 404 Not Found Error Page
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';

http_response_code(404);
$tieuDe = '404 - Page Not Found';
$trang = '404';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <div style="text-align: center; padding: 60px 20px;">
    <h1 style="font-size: 4rem; color: #d97706; margin-bottom: 10px;">404</h1>
    <h2>Requested Page Not Found</h2>
    <p style="max-width: 600px; margin: 15px auto; color: #64748b; font-size: 1.1rem;">
      The textbook or URL you are looking for may have been removed, had its identifier changed, or is temporarily unavailable on BookNest.
    </p>
    <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
      <a href="index.php" class="nut nut--chinh">&larr; Back to Home</a>
      <a href="danh-sach.php" class="nut nut--vien">Explore Book Catalog</a>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
