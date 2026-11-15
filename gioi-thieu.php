<?php
/**
 * gioi-thieu.php - Introduction to BookNest and Team 12 members
 * Team 12 - Class 24CNTT3 - University of Science and Education, Danang
 */

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'About Team 12 & BookNest Project';
$trang = 'gioi-thieu';
$moTaTrang = 'Introduction to Team 12 Class 24CNTT3 and BookNest online textbook bookstore project for UED students.';

require __DIR__ . '/inc/header.php';
?>

<main class="noi-dung-chinh khung-chua">
  <h1>About Development Team 12 &amp; BookNest Project</h1>

  <section>
    <h2>Project Overview</h2>
    <p class="doan-van-dai">The <strong>BookNest</strong> project is built within the <em>Web Design and Programming</em> curriculum, Semester 1, Academic Year 2026 - 2027 at the Faculty of Mathematics &amp; IT, University of Science and Education - Danang University.</p>
    <p class="doan-van-dai">In Chapter 7, the team specializes in <strong>Web Application Security</strong> following the <em>Defense-in-Depth</em> architecture: integrating CSRF tokens across all 19 POST forms and fetch APIs, securing sessions (HttpOnly, SameSite=Lax, 30-minute TTL), setting strict HTTP security headers (CSP script-src 'self', frame-ancestors 'none', nosniff, removed X-Powered-By), configuring Apache <code>.htaccess</code> to block sensitive files, and enforcing least-privilege MySQL access.</p>
    
    <h3>Vision and Mission</h3>
    <ul class="doan-van-dai">
      <li><strong>Vision:</strong> Become the premier digital hub for textbook discovery and distribution for students at the University of Science and Education - Danang University.</li>
      <li><strong>Mission:</strong> Digitalize the textbook purchasing, lending, and sharing workflows, minimizing academic costs and elevating the learning experience for future educators and software engineers.</li>
    </ul>
  </section>

  <section>
    <h2>Team 12 Members (Class 24CNTT3)</h2>
    <p style="color: #64748b; margin-bottom: 24px;">Click on <strong>"View Profile"</strong> for each member to inspect their individual task matrix, security implementations, and database responsibilities:</p>

    <div class="danh-sach-thanh-vien">
      
      <!-- Member 1: Xaiyasith Yoi -->
      <article class="the-thanh-vien">
        <figure>
          <img src="images/yoi-avatar.png" alt="Student portrait of Xaiyasith Yoi" width="120" height="120" loading="lazy">
          <figcaption>
            <strong>Xaiyasith Yoi</strong>
            <small>Student ID: 3120224189</small>
          </figcaption>
        </figure>
        <p style="margin: 10px 0 16px;">
          <a href="https://github.com/xys-3y7" target="_blank" rel="noopener noreferrer" class="github-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: xys-3y7</span>
          </a>
        </p>
        <a href="thanhvien/3120224189_yoi/gioithieu.php" class="nut nut--chinh">View Profile &rarr;</a>
      </article>

      <!-- Member 2: Phommaket Hatsady -->
      <article class="the-thanh-vien">
        <figure>
          <img src="images/hatsady-avatar.png" alt="Student portrait of Phommaket Hatsady" width="120" height="120" loading="lazy">
          <figcaption>
            <strong>Phommaket Hatsady</strong>
            <small>Student ID: 3120224181</small>
          </figcaption>
        </figure>
        <p style="margin: 10px 0 16px;">
          <a href="https://github.com/daimobile33076-eng" target="_blank" rel="noopener noreferrer" class="github-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: daimobile33076-eng</span>
          </a>
        </p>
        <a href="thanhvien/3120224181_hatsady/gioithieu.php" class="nut nut--chinh">View Profile &rarr;</a>
      </article>

      <!-- Member 3: Vongsena Sauphasith -->
      <article class="the-thanh-vien">
        <figure>
          <img src="images/sauphasith-avatar.png" alt="Student portrait of Vongsena Sauphasith" width="120" height="120" loading="lazy">
          <figcaption>
            <strong>Vongsena Sauphasith</strong>
            <small>Student ID: 3120224186</small>
          </figcaption>
        </figure>
        <p style="margin: 10px 0 16px;">
          <a href="https://github.com/happy2072005-prog" target="_blank" rel="noopener noreferrer" class="github-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: happy2072005-prog</span>
          </a>
        </p>
        <a href="thanhvien/3120224186_sauphasith/gioithieu.php" class="nut nut--chinh">View Profile &rarr;</a>
      </article>

      <!-- Member 4: Nguyễn Thị Sinh -->
      <article class="the-thanh-vien">
        <figure>
          <img src="images/sinh-avatar.png" alt="Student portrait of Nguyen Thi Sinh" width="120" height="120" loading="lazy">
          <figcaption>
            <strong>Nguyễn Thị Sinh</strong>
            <small>Student ID: 3120223169</small>
          </figcaption>
        </figure>
        <p style="margin: 10px 0 16px;">
          <a href="https://github.com/chingg2005" target="_blank" rel="noopener noreferrer" class="github-pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <span>GitHub: chingg2005</span>
          </a>
        </p>
        <a href="thanhvien/3120223169_sinh/gioithieu.php" class="nut nut--chinh">View Profile &rarr;</a>
      </article>

    </div>
  </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
