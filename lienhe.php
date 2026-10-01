<?php
require_once 'session_check.php';
require_once 'config.php';

use App\Helpers\CsrfHelper;
use App\Helpers\RateLimiter;
use App\Helpers\NotificationHelper;
use App\Helpers\Logger;
use App\Core\Database;

$error = '';
$success = '';

// Load shop settings từ database hoặc giá trị mặc định
if (!isset($shopSettings)) {
    $shopSettings = [];
    try {
        $dbConn = Database::getInstance();
        if ($dbConn instanceof PDO) {
            $shopSettings = $dbConn->query("SELECT setting_key, setting_value FROM shop_settings")
                                   ->fetchAll(PDO::FETCH_KEY_PAIR);
        }
    } catch (Exception $e) { /* silent fail */ }
}

$shopName    = htmlspecialchars($shopSettings['shop_name']    ?? 'PC Store');
$shopHotline = htmlspecialchars($shopSettings['shop_hotline'] ?? '1800 6975');
$shopEmail   = htmlspecialchars($shopSettings['shop_email']   ?? 'contact@pcstore.vn');
$shopAddress = htmlspecialchars($shopSettings['shop_address'] ?? '123 Cầu Giấy, Cầu Giấy, Hà Nội');
$shopFb      = htmlspecialchars($shopSettings['shop_facebook'] ?? '#');
$shopZalo    = htmlspecialchars($shopSettings['shop_zalo']     ?? '#');

// Tự động điền thông tin nếu người dùng đã đăng nhập
$userLoggedIn = $_SESSION['user'] ?? null;
$prefillName  = '';
$prefillEmail = '';
$prefillPhone = '';

if ($userLoggedIn && is_array($userLoggedIn)) {
    $prefillName  = $userLoggedIn['full_name'] ?? $userLoggedIn['username'] ?? '';
    $prefillEmail = $userLoggedIn['email'] ?? '';
    $prefillPhone = $userLoggedIn['phone'] ?? '';
}

// Xử lý gửi biểu mẫu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        CsrfHelper::verify();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    if (!$error) {
        $rlKey = RateLimiter::ipKey('contact');
        try {
            RateLimiter::check($rlKey);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if (!$error) {
            $name        = trim($_POST['name'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $phone       = trim($_POST['phone'] ?? '');
            $subject     = trim($_POST['subject'] ?? 'Tư vấn cấu hình PC');
            $message_txt = trim($_POST['message'] ?? '');

            if ($name === '' || $email === '' || $message_txt === '') {
                $error = 'Vui lòng điền đầy đủ các thông tin bắt buộc (*).';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Địa chỉ Email không đúng định dạng. Vui lòng kiểm tra lại.';
            } else {
                // Tạo thông báo xác nhận nếu user đang đăng nhập
                try {
                    $dbConn = Database::getInstance();
                    $userId = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? null);
                    if ($userId && $dbConn instanceof PDO) {
                        NotificationHelper::send(
                            $dbConn,
                            (int)$userId,
                            '📬 Đã nhận yêu cầu hỗ trợ: ' . $subject,
                            'Chào ' . $name . ', PC Store đã nhận được yêu cầu hỗ trợ của bạn. Chuyên viên kỹ thuật sẽ liên hệ phản hồi trong thời gian sớm nhất!',
                            'info'
                        );
                    }
                } catch (Exception $e) { /* silent fail */ }

                // Ghi log hệ thống
                try {
                    if (class_exists('App\Helpers\Logger')) {
                        Logger::info("Yêu cầu hỗ trợ mới: [{$subject}] Tên: {$name} - Email: {$email} - SĐT: {$phone} - Nội dung: {$message_txt}");
                    }
                } catch (Exception $e) { /* silent fail */ }

                $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi đã tiếp nhận yêu cầu và sẽ phản hồi trong thời gian sớm nhất.';
                RateLimiter::record($rlKey);
            }
        }
    }
}

$pageTitle       = 'Trung Tâm Hỗ Trợ 24/7 & Liên Hệ - ' . $shopName;
$pageDescription = 'Đội ngũ chuyên viên kỹ thuật và tư vấn viên của PC Store luôn sẵn sàng giải đáp thắc mắc, tư vấn xây dựng cấu hình và hỗ trợ bảo hành nhanh nhất.';

include 'app/views/header.php';
?>

<style>
/* ================================================================
   TRANG HỖ TRỢ / LIÊN HỆ — PC STORE REDESIGN
   ================================================================ */
:root {
    --sp-blue-dark:  #081736;
    --sp-blue-mid:   #0f2e6e;
    --sp-blue-vivid: #2563eb;
    --sp-blue-light: #eff6ff;
}

.support-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px 16px 64px;
    font-family: var(--font-sans, 'Outfit', 'Segoe UI', Arial, sans-serif);
}

/* ── 1. Hero Banner ── */
.support-hero {
    position: relative;
    border-radius: 24px;
    padding: 48px 24px 72px;
    text-align: center;
    color: #ffffff;
    overflow: hidden;
    background: radial-gradient(circle at 85% 20%, rgba(59, 130, 246, 0.32) 0%, transparent 45%),
                radial-gradient(circle at 15% 85%, rgba(37, 99, 235, 0.28) 0%, transparent 45%),
                linear-gradient(135deg, #071530 0%, #0d285c 45%, #1d4ed8 100%);
    box-shadow: 0 12px 36px -8px rgba(15, 23, 42, 0.2);
}

.support-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #bfdbfe;
    padding: 6px 18px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.support-hero h1 {
    font-size: clamp(24px, 4vw, 36px);
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 10px;
    letter-spacing: -0.02em;
}

.support-hero p {
    font-size: 14.5px;
    color: rgba(255, 255, 255, 0.82);
    max-width: 680px;
    margin: 0 auto;
    line-height: 1.65;
}

/* ── 2. Top 4 Contact Info Cards ── */
.support-quick-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: -34px;
    margin-bottom: 28px;
    position: relative;
    z-index: 2;
}

.support-card {
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 16px;
    padding: 20px 18px;
    box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.06);
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
}

.support-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 30px -4px rgba(37, 99, 235, 0.12);
    border-color: #93c5fd;
}

.support-card-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 18px;
}

.icon-blue   { background: #eff6ff; color: #2563eb; }
.icon-green  { background: #f0fdf4; color: #16a34a; }
.icon-amber  { background: #fffbeb; color: #d97706; }
.icon-purple { background: #faf5ff; color: #9333ea; }

.support-card-title {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--txt-primary, #0f172a);
    margin: 0 0 3px;
}

.support-card-sub {
    font-size: 12.5px;
    color: var(--txt-secondary, #64748b);
    margin: 0 0 6px;
}

.support-card-value {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--accent, #2563eb);
    margin-top: auto;
}

/* ── 3. Main Workspace: Form (Left) & Sidebar (Right) ── */
.support-main-layout {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
    gap: 24px;
    margin-bottom: 36px;
    align-items: start;
}

.support-panel {
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 20px;
    padding: 26px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
}

.support-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.support-panel-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 700;
    color: var(--txt-primary, #0f172a);
    margin: 0;
}

.support-panel-title svg {
    color: var(--accent, #2563eb);
    flex-shrink: 0;
}

.support-panel-sub {
    font-size: 13px;
    color: var(--txt-secondary, #64748b);
    margin: 0 0 20px;
}

/* Form controls */
.support-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}

.support-form-group {
    display: flex;
    flex-direction: column;
}

.support-form-group.full-width {
    grid-column: 1 / -1;
    margin-bottom: 16px;
}

.support-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--txt-primary, #1e293b);
    margin-bottom: 6px;
}

.support-label .required {
    color: #ef4444;
}

.support-input,
.support-select,
.support-textarea {
    width: 100%;
    padding: 11px 14px;
    font-size: 14px;
    font-family: inherit;
    color: var(--txt-primary, #0f172a);
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border, #cbd5e1);
    border-radius: 10px;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.support-input:focus,
.support-select:focus,
.support-textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    outline: none;
}

.support-select {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px;
    cursor: pointer;
}

.support-textarea {
    resize: vertical;
    min-height: 105px;
    line-height: 1.5;
}

.support-btn-submit {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.support-btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(37, 99, 235, 0.42);
    filter: brightness(1.03);
}

.support-btn-submit:active {
    transform: translateY(0);
}

/* Alert boxes */
.support-alert {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 13.5px;
    display: flex;
    align-items: center;
    gap: 10px;
    line-height: 1.5;
}
.support-alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}
.support-alert-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #15803d;
}

/* ── Sidebar Right (Map & Quick Channels) ── */
.support-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.support-badge-pill {
    font-size: 12px;
    font-weight: 600;
    color: #2563eb;
    background: #eff6ff;
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid #dbeafe;
    text-decoration: none;
}

.support-map-wrapper {
    width: 100%;
    height: 205px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border, #e2e8f0);
}

.support-map-wrapper iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}

/* Quick Channels 2x2 */
.support-channels-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 14px;
}

.channel-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    color: #ffffff !important;
    text-decoration: none !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.channel-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.15);
}

.channel-btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 15px;
}

.channel-btn-info {
    display: flex;
    flex-direction: column;
    line-height: 1.25;
}

.channel-btn-sub {
    font-size: 11px;
    opacity: 0.88;
    font-weight: 500;
}

.channel-btn-main {
    font-size: 13.5px;
    font-weight: 700;
}

.btn-hotline   { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.btn-zalo      { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
.btn-messenger { background: linear-gradient(135deg, #1877f2 0%, #1d4ed8 100%); }
.btn-website   { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

/* ── 4. Showroom & Trung Tâm Bảo Hành ── */
.support-section-header {
    text-align: center;
    margin-bottom: 24px;
}

.support-section-title {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
    font-weight: 700;
    color: var(--txt-primary, #0f172a);
    margin: 0;
}

.support-section-title svg {
    color: var(--accent, #2563eb);
}

.support-section-sub {
    font-size: 13.5px;
    color: var(--txt-secondary, #64748b);
    margin: 6px auto 0;
    max-width: 600px;
}

.support-showrooms-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.showroom-card {
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    display: flex;
    flex-direction: column;
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    cursor: pointer;
}

.showroom-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 26px -4px rgba(37, 99, 235, 0.12);
    border-color: #93c5fd;
}

.showroom-pill {
    align-self: flex-start;
    font-size: 11.5px;
    font-weight: 700;
    color: #2563eb;
    background: #eff6ff;
    padding: 3px 12px;
    border-radius: 999px;
    margin-bottom: 12px;
}

.showroom-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--txt-primary, #0f172a);
    margin: 0 0 14px;
}

.showroom-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.showroom-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 13px;
    color: var(--txt-secondary, #475569);
    line-height: 1.5;
}

.showroom-item svg {
    flex-shrink: 0;
    color: #2563eb;
    margin-top: 3px;
}

/* ── 5. Câu Hỏi Thường Gặp (FAQ) ── */
.support-faq-wrap {
    margin-bottom: 40px;
}

.support-faq-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 20px;
}

.faq-card {
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 20px 22px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
}

.faq-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    border-color: #cbd5e1;
}

.faq-question {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 14.5px;
    font-weight: 700;
    color: var(--txt-primary, #0f172a);
    margin: 0 0 8px;
    line-height: 1.4;
}

.faq-question svg {
    flex-shrink: 0;
    color: #2563eb;
    margin-top: 2px;
}

.faq-answer {
    font-size: 13.5px;
    color: var(--txt-secondary, #64748b);
    line-height: 1.6;
    margin: 0;
    padding-left: 26px;
}

/* ── 6. Bottom Trust Badge Bar ── */
.support-trust-bar {
    background: linear-gradient(135deg, #071329 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 22px 28px;
    color: #ffffff;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    align-items: center;
    box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.15);
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 14px;
}

.trust-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #60a5fa;
    flex-shrink: 0;
}

.trust-content {
    display: flex;
    flex-direction: column;
}

.trust-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
}

.trust-desc {
    font-size: 11.5px;
    color: rgba(255, 255, 255, 0.65);
    margin: 2px 0 0;
}

/* ── Dark Mode Adaptations ── */
[data-theme="dark"] .support-card,
[data-theme="dark"] .support-panel,
[data-theme="dark"] .showroom-card,
[data-theme="dark"] .faq-card {
    background: var(--bg-surface, #1e293b);
    border-color: var(--border, #334155);
}

[data-theme="dark"] .support-card:hover,
[data-theme="dark"] .showroom-card:hover {
    border-color: #3b82f6;
}

[data-theme="dark"] .support-input,
[data-theme="dark"] .support-select,
[data-theme="dark"] .support-textarea {
    background: #0f172a;
    border-color: #334155;
    color: #f1f5f9;
}

[data-theme="dark"] .support-badge-pill,
[data-theme="dark"] .showroom-pill {
    background: rgba(37, 99, 235, 0.2);
    color: #93c5fd;
    border-color: rgba(37, 99, 235, 0.3);
}

[data-theme="dark"] .icon-blue   { background: rgba(37, 99, 235, 0.2); color: #60a5fa; }
[data-theme="dark"] .icon-green  { background: rgba(22, 163, 74, 0.2);  color: #4ade80; }
[data-theme="dark"] .icon-amber  { background: rgba(217, 119, 6, 0.2);  color: #fbbf24; }
[data-theme="dark"] .icon-purple { background: rgba(147, 51, 234, 0.2); color: #c084fc; }

/* ── Responsive Media Queries ── */
@media (max-width: 1024px) {
    .support-quick-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    .support-main-layout {
        grid-template-columns: 1fr;
    }
    .support-showrooms-grid {
        grid-template-columns: 1fr;
    }
    .support-faq-grid {
        grid-template-columns: 1fr;
    }
    .support-trust-bar {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .support-quick-cards {
        grid-template-columns: 1fr;
    }
    .support-form-grid {
        grid-template-columns: 1fr;
    }
    .support-channels-grid {
        grid-template-columns: 1fr;
    }
    .support-trust-bar {
        grid-template-columns: 1fr;
    }
    .support-panel {
        padding: 20px 16px;
    }
}
</style>

<div class="support-container">

    <!-- 1. HERO BANNER -->
    <div class="support-hero">
        <div class="support-hero-badge">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            TRUNG TÂM HỖ TRỢ 24/7
        </div>
        <h1>Liên Hệ Với Chúng Tôi</h1>
        <p>Đội ngũ chuyên viên kỹ thuật và tư vấn viên của PC Store luôn sẵn sàng giải đáp thắc mắc, tư vấn xây dựng cấu hình và hỗ trợ bảo hành nhanh nhất.</p>
    </div>

    <!-- 2. TOP 4 QUICK INFO CARDS -->
    <div class="support-quick-cards">
        <!-- Hotline -->
        <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $shopHotline); ?>" class="support-card">
            <div class="support-card-icon icon-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div class="support-card-title">Hotline Hỗ Trợ</div>
            <div class="support-card-sub">Tư vấn mua hàng & CSKH</div>
            <div class="support-card-value"><?php echo $shopHotline; ?></div>
        </a>

        <!-- Email -->
        <a href="mailto:<?php echo $shopEmail; ?>" class="support-card">
            <div class="support-card-icon icon-green">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
            <div class="support-card-title">Email Hỗ Trợ</div>
            <div class="support-card-sub">Phản hồi trong 2 giờ</div>
            <div class="support-card-value"><?php echo $shopEmail; ?></div>
        </a>

        <!-- Showroom chính -->
        <div class="support-card">
            <div class="support-card-icon icon-amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="support-card-title">Showroom Chính</div>
            <div class="support-card-sub"><?php echo $shopAddress; ?></div>
            <div class="support-card-value">Mở cửa đón khách</div>
        </div>

        <!-- Giờ hoạt động -->
        <div class="support-card">
            <div class="support-card-icon icon-purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="support-card-title">Giờ Hoạt Động</div>
            <div class="support-card-sub">T2 - CN (Cả lễ)</div>
            <div class="support-card-value">08:00 - 21:30</div>
        </div>
    </div>

    <!-- 3. MAIN WORKSPACE: FORM & SIDEBAR -->
    <div class="support-main-layout">

        <!-- Form "Gửi Lời Nhắn Trực Tuyến" -->
        <div class="support-panel">
            <div class="support-panel-header">
                <h2 class="support-panel-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Gửi Lời Nhắn Trực Tuyến
                </h2>
            </div>
            <p class="support-panel-sub">Điền thông tin vào form dưới đây, chúng tôi sẽ phản hồi sớm nhất có thể.</p>

            <?php if (!empty($error)): ?>
                <div class="support-alert support-alert-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="support-alert support-alert-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <div><?php echo htmlspecialchars($success); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo CsrfHelper::field(); ?>

                <div class="support-form-grid">
                    <div class="support-form-group">
                        <label class="support-label">Họ và tên <span class="required">*</span></label>
                        <input type="text" name="name" class="support-input" placeholder="Ví dụ: Lê Lợi" required value="<?php echo htmlspecialchars($_POST['name'] ?? $prefillName); ?>">
                    </div>

                    <div class="support-form-group">
                        <label class="support-label">Địa chỉ Email <span class="required">*</span></label>
                        <input type="email" name="email" class="support-input" placeholder="nguyenvanan1202202@gmail.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? $prefillEmail); ?>">
                    </div>

                    <div class="support-form-group">
                        <label class="support-label">Số điện thoại</label>
                        <input type="tel" name="phone" class="support-input" placeholder="0909 xxx xxx" value="<?php echo htmlspecialchars($_POST['phone'] ?? $prefillPhone); ?>">
                    </div>

                    <div class="support-form-group">
                        <label class="support-label">Chủ đề cần hỗ trợ</label>
                        <select name="subject" class="support-select">
                            <?php
                            $subjects = [
                                'Tư vấn cấu hình PC',
                                'Bảo hành & Sửa chữa',
                                'Tra cứu đơn hàng',
                                'Hỗ trợ kỹ thuật phần mềm/phần cứng',
                                'Góp ý & Khiếu nại dịch vụ',
                                'Khác'
                            ];
                            $selectedSub = $_POST['subject'] ?? 'Tư vấn cấu hình PC';
                            foreach ($subjects as $sub) {
                                $sel = ($selectedSub === $sub) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($sub) . "\" $sel>" . htmlspecialchars($sub) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="support-form-group full-width">
                    <label class="support-label">Nội dung chi tiết <span class="required">*</span></label>
                    <textarea name="message" class="support-textarea" rows="4" required placeholder="Mô tả chi tiết nhu cầu hoặc câu hỏi của bạn để chúng tôi hỗ trợ tốt nhất..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="support-btn-submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Gửi Tin Nhắn Cho Chúng Tôi
                </button>
            </form>
        </div>

        <!-- Sidebar Widgets: Map & Kênh kết nối -->
        <div class="support-sidebar">

            <!-- Vị Trí Showroom -->
            <div class="support-panel">
                <div class="support-panel-header">
                    <h3 class="support-panel-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        Vị Trí Showroom
                    </h3>
                    <a href="#showroom-hn" class="support-badge-pill" id="mapTargetLabel">Hà Nội</a>
                </div>
                <div class="support-map-wrapper">
                    <iframe id="supportGMap" src="https://maps.google.com/maps?q=123+C%E1%BA%A7u+Gi%E1%BA%A5y,+H%C3%A0+N%E1%BB%99i&t=&z=15&ie=UTF8&iwloc=&output=embed" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <!-- Kênh Kết Nối Nhanh -->
            <div class="support-panel">
                <div class="support-panel-header">
                    <h3 class="support-panel-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        Kênh Kết Nối Nhanh
                    </h3>
                </div>

                <div class="support-channels-grid">
                    <!-- Gọi Hotline -->
                    <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $shopHotline); ?>" class="channel-btn btn-hotline">
                        <div class="channel-btn-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div class="channel-btn-info">
                            <span class="channel-btn-sub">Tổng đài</span>
                            <span class="channel-btn-main">Gọi Hotline</span>
                        </div>
                    </a>

                    <!-- Zalo OA -->
                    <a href="<?php echo (!empty($shopZalo) && $shopZalo !== '#') ? $shopZalo : 'https://zalo.me'; ?>" target="_blank" rel="noopener noreferrer" class="channel-btn btn-zalo">
                        <div class="channel-btn-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/></svg>
                        </div>
                        <div class="channel-btn-info">
                            <span class="channel-btn-sub">Phản hồi 24/7</span>
                            <span class="channel-btn-main">Chat Zalo OA</span>
                        </div>
                    </a>

                    <!-- Messenger -->
                    <a href="<?php echo (!empty($shopFb) && $shopFb !== '#') ? $shopFb : 'https://m.me'; ?>" target="_blank" rel="noopener noreferrer" class="channel-btn btn-messenger">
                        <div class="channel-btn-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </div>
                        <div class="channel-btn-info">
                            <span class="channel-btn-sub">Fanpage</span>
                            <span class="channel-btn-main">Messenger</span>
                        </div>
                    </a>

                    <!-- Chat Website -->
                    <a href="<?php echo BASE_URL; ?>chat.php" class="channel-btn btn-website">
                        <div class="channel-btn-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <div class="channel-btn-info">
                            <span class="channel-btn-sub">Trò chuyện</span>
                            <span class="channel-btn-main">Chat Website</span>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- 4. HỆ THỐNG SHOWROOM & TRUNG TÂM BẢO HÀNH -->
    <div class="support-section-header">
        <h2 class="support-section-title">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/></svg>
            Hệ Thống Showroom & Trung Tâm Bảo Hành
        </h2>
    </div>

    <div class="support-showrooms-grid">
        <!-- Showroom 1: Hà Nội -->
        <div class="showroom-card" id="showroom-hn" onclick="switchMap('123+C%E1%BA%A7u+Gi%E1%BA%A5y,+H%C3%A0+N%E1%BB%99i', 'Hà Nội')">
            <div class="showroom-pill">Hà Nội</div>
            <h3 class="showroom-name">Showroom Cầu Giấy (Trụ sở chính)</h3>
            <ul class="showroom-list">
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>123 Đường Cầu Giấy, Q. Cầu Giấy, Hà Nội</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>Hotline: 1800 6975 (Nhánh 1)</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>08:00 - 21:30 (Cả thứ 7 & Chủ nhật)</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                    <span>Có chỗ đỗ ô tô & xe máy miễn phí</span>
                </li>
            </ul>
        </div>

        <!-- Showroom 2: TP. Hồ Chí Minh -->
        <div class="showroom-card" id="showroom-hcm" onclick="switchMap('456+L%C3%AA+H%E1%BB%93ng+Phong,+Ph%C6%B0%E1%BB%9Dng+1,+Qu%E1%BA%ADn+10,+H%E1%BB%93+Ch%C3%AD+Minh', 'TP. Hồ Chí Minh')">
            <div class="showroom-pill">TP. Hồ Chí Minh</div>
            <h3 class="showroom-name">Showroom Quận 10</h3>
            <ul class="showroom-list">
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>456 Đường Lê Hồng Phong, Phường 1, Quận 10, TP. HCM</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>Hotline: 1800 6975 (Nhánh 2)</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>08:00 - 21:30 (Cả thứ 7 & Chủ nhật)</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    <span>Tiếp nhận bảo hành & Lắp ráp PC lấy ngay</span>
                </li>
            </ul>
        </div>

        <!-- Showroom 3: Đà Nẵng -->
        <div class="showroom-card" id="showroom-dn" onclick="switchMap('78+Nguy%E1%BB%85n+V%C4%83n+Linh,+H%E1%BA%A3i+Ch%C3%A2u,+%C4%90%C3%A0+N%E1%BA%B5ng', 'Đà Nẵng')">
            <div class="showroom-pill">Đà Nẵng</div>
            <h3 class="showroom-name">Showroom Hải Châu</h3>
            <ul class="showroom-list">
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>78 Đường Nguyễn Văn Linh, Q. Hải Châu, TP. Đà Nẵng</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>Hotline: 1800 6975 (Nhánh 3)</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>08:30 - 21:00 (Tất cả các ngày)</span>
                </li>
                <li class="showroom-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    <span>Giao hàng siêu tốc trong 2 giờ nội thành</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- 5. CÂU HỎI THƯỜNG GẶP (FAQ) -->
    <div class="support-faq-wrap">
        <div class="support-section-header">
            <h2 class="support-section-title">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Câu Hỏi Thường Gặp (FAQ)
            </h2>
            <p class="support-section-sub">Những thắc mắc phổ biến nhất khi mua hàng và sử dụng dịch vụ tại PC Store</p>
        </div>

        <div class="support-faq-grid">
            <div class="faq-card">
                <h4 class="faq-question">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Tôi có được kiểm tra hàng trước khi thanh toán không?
                </h4>
                <p class="faq-answer">Có! Bạn hoàn toàn được đồng kiểm tra sản phẩm với nhân viên giao hàng về ngoại quan, tem niêm phong và đúng chủng loại trước khi thanh toán.</p>
            </div>

            <div class="faq-card">
                <h4 class="faq-question">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Chính sách bảo hành linh kiện như thế nào?
                </h4>
                <p class="faq-answer">Toàn bộ sản phẩm được phân phối là hàng chính hãng 100%, bảo hành theo tiêu chuẩn nhà sản xuất từ 12 - 36 tháng. Hỗ trợ gửi bảo hành theo hình thức đổi mới trong 30 ngày.</p>
            </div>

            <div class="faq-card">
                <h4 class="faq-question">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Đổi trả hàng trong bao lâu nếu sản phẩm lỗi?
                </h4>
                <p class="faq-answer">PC Store đổi mới 1 đổi 1 trong vòng 7 ngày đầu tiên kể từ khi nhận hàng nếu có lỗi phần cứng từ phía nhà sản xuất.</p>
            </div>

            <div class="faq-card">
                <h4 class="faq-question">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Tôi có được hỗ trợ lắp ráp và cài đặt PC miễn phí không?
                </h4>
                <p class="faq-answer">Khi mua trọn bộ linh kiện (Build PC) tại PC Store, bạn sẽ được kỹ thuật viên lắp ráp hoàn thiện, test nhiệt độ và cài đặt phần mềm cơ bản hoàn toàn miễn phí.</p>
            </div>
        </div>
    </div>

    <!-- 6. BOTTOM TRUST BADGE BAR -->
    <div class="support-trust-bar">
        <div class="trust-item">
            <div class="trust-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div class="trust-content">
                <span class="trust-title">100% Chính Hãng</span>
                <span class="trust-desc">Toàn bộ sản phẩm uy tín</span>
            </div>
        </div>

        <div class="trust-item">
            <div class="trust-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
            </div>
            <div class="trust-content">
                <span class="trust-title">Đổi Trả 7 Ngày</span>
                <span class="trust-desc">1 đổi 1 nếu lỗi NSX</span>
            </div>
        </div>

        <div class="trust-item">
            <div class="trust-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="13" x="1" y="3" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <div class="trust-content">
                <span class="trust-title">Giao Siêu Tốc 2H</span>
                <span class="trust-desc">Nội thành hỏa tốc</span>
            </div>
        </div>

        <div class="trust-item">
            <div class="trust-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            </div>
            <div class="trust-content">
                <span class="trust-title">Hỗ Trợ Trọn Đời</span>
                <span class="trust-desc">Kỹ thuật viên nhiệt tình</span>
            </div>
        </div>
    </div>

</div>

<script>
function switchMap(query, label) {
    const iframe = document.getElementById('supportGMap');
    const badge = document.getElementById('mapTargetLabel');
    if (iframe) {
        iframe.src = 'https://maps.google.com/maps?q=' + query + '&t=&z=15&ie=UTF8&iwloc=&output=embed';
    }
    if (badge) {
        badge.textContent = label;
    }
}
</script>

<?php include 'app/views/footer.php'; ?>
