<?php
/**
 * inc/ngon-ngu.php - Language & English Subsystem Engine
 * BookNest - UED Student Online Bookstore
 */

// Initialize or update language preference
if (isset($_GET['lang'])) {
    $langChoice = strtolower(trim($_GET['lang']));
    if (in_array($langChoice, ['en', 'vi'], true)) {
        $_SESSION['booknest_lang'] = $langChoice;
        setcookie('booknest_lang', $langChoice, time() + 86400 * 365, '/', '', false, true);
    }
}

/**
 * Get current active language code ('en' or 'vi')
 */
function ngonNguHienTai(): string
{
    return $_SESSION['booknest_lang'] ?? $_COOKIE['booknest_lang'] ?? 'en';
}

/**
 * Check if English mode is active
 */
function laTiengAnh(): bool
{
    return ngonNguHienTai() === 'en';
}

/**
 * Resolve bilingual book title (prefers English if active)
 */
function tieuDeSach(array|object $book): string
{
    $arr = is_array($book) ? $book : (array)$book;
    if (laTiengAnh() && !empty($arr['ten_en'])) {
        return $arr['ten_en'];
    }
    if (laTiengAnh() && !empty($arr['title_en'])) {
        return $arr['title_en'];
    }
    return $arr['ten'] ?? $arr['title'] ?? '';
}

/**
 * Resolve bilingual book author
 */
function tacGiaSach(array|object $book): string
{
    $arr = is_array($book) ? $book : (array)$book;
    if (laTiengAnh() && !empty($arr['tac_gia_en'])) {
        return $arr['tac_gia_en'];
    }
    if (laTiengAnh() && !empty($arr['author_en'])) {
        return $arr['author_en'];
    }
    return $arr['tac_gia'] ?? $arr['author'] ?? 'BookNest Editorial';
}

/**
 * Resolve bilingual book description
 */
function moTaSach(array|object $book): string
{
    $arr = is_array($book) ? $book : (array)$book;
    if (laTiengAnh() && !empty($arr['mo_ta_en'])) {
        return $arr['mo_ta_en'];
    }
    if (laTiengAnh() && !empty($arr['description_en'])) {
        return $arr['description_en'];
    }
    return $arr['mo_ta'] ?? $arr['description'] ?? '';
}

/**
 * Resolve bilingual category name
 */
function tenDanhMuc(array|object $category): string
{
    $arr = is_array($category) ? $category : (array)$category;
    if (laTiengAnh() && !empty($arr['ten_en'])) {
        return $arr['ten_en'];
    }
    if (laTiengAnh() && !empty($arr['name_en'])) {
        return $arr['name_en'];
    }
    return $arr['ten'] ?? $arr['name'] ?? '';
}

/**
 * Resolve bilingual category description
 */
function moTaDanhMuc(array|object $category): string
{
    $arr = is_array($category) ? $category : (array)$category;
    if (laTiengAnh() && !empty($arr['mo_ta_en'])) {
        return $arr['mo_ta_en'];
    }
    if (laTiengAnh() && !empty($arr['description_en'])) {
        return $arr['description_en'];
    }
    return $arr['mo_ta'] ?? $arr['description'] ?? '';
}
