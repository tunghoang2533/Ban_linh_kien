<?php
// AssetHelper autoloaded via PSR-4 + class_alias
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    // ── Dynamic SEO Meta Tags ──────────────────────────────────
    // Các trang có thể override: $pageTitle, $pageDescription, $pageImage, $pageCanonical
    $seoTitle       = !empty($pageTitle)       ? $pageTitle . ' | PC Store'       : 'PC Store - Linh kiện máy tính chuyên nghiệp';
    $seoDescription = !empty($pageDescription) ? $pageDescription                 : 'Mua linh kiện máy tính chính hãng: CPU, GPU, RAM, SSD, mainboard giá tốt. Giao hàng nhanh, bảo hành chính hãng.';
    $seoImage       = !empty($pageImage)       ? $pageImage                       : BASE_URL . 'public/img/og-default.jpg';
    $seoCanonical   = !empty($pageCanonical)   ? $pageCanonical                   : BASE_URL . ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/');
    ?>

    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($seoCanonical); ?>">

    <!-- Open Graph (Facebook, Zalo, Telegram) -->
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta property="og:image"       content="<?php echo htmlspecialchars($seoImage); ?>">
    <meta property="og:url"         content="<?php echo htmlspecialchars($seoCanonical); ?>">
    <meta property="og:locale"      content="vi_VN">
    <meta property="og:site_name"   content="PC Store">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta name="twitter:image"       content="<?php echo htmlspecialchars($seoImage); ?>">

    <?php if (!empty($jsonLd)): ?>
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json"><?php echo $jsonLd; ?></script>
    <?php endif; ?>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="<?php echo AssetHelper::url('public/css/style.css', true); ?>">

    <style>
        /* ================================================================
           PC Store — Header v2.0 (Design System Tokens applied)
           Font: Outfit | Accent: #2563eb | Bg: #ffffff / #f8fafc
           ================================================================ */

        /* ── Base override ── */
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Outfit', 'Segoe UI', Arial, sans-serif;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ================================================================
           TOP BAR
           ================================================================ */
        .top-nav {
            background: #0f172a;
            padding: 0;
            border-bottom: none;
        }
        .top-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 36px;
            gap: 0;
        }
        .top-nav .top-nav-left {
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .top-nav .top-nav-left strong { color: #93c5fd; }
        .top-nav .top-nav-left i { color: #3b82f6; font-size: 11px; }
        .top-nav .top-nav-right {
            display: flex;
            align-items: center;
        }
        .top-nav a {
            color: #64748b;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            padding: 0 14px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-left: 1px solid rgba(255,255,255,0.06);
            transition: color .16s, background .16s;
        }
        .top-nav a:first-child { border-left: none; }
        .top-nav a:hover { color: #e2e8f0; background: rgba(255,255,255,0.04); }

        /* ================================================================
           MAIN HEADER
           ================================================================ */
        header {
            background: #ffffff;
            padding: 0;
            border-bottom: 1px solid #e8eef4;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 0 #f1f5f9, 0 2px 16px rgba(15,23,42,0.06);
        }
        .header-inner {
            display: flex;
            align-items: center;
            gap: 20px;
            height: 68px;
        }

        /* ── Logo ── */
        .logo { flex-shrink: 0; }
        .logo a {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: inherit;
        }
        .logo-icon {
            width: 38px;
            height: 38px;
            background: #2563eb;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 17px;
            flex-shrink: 0;
            transition: transform 0.18s ease;
        }
        .logo a:hover .logo-icon { transform: rotate(-8deg) scale(1.06); }
        .logo-text {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .logo-text span { color: #2563eb; }
        /* Hide img logo — use text logo instead */
        .logo img { display: none; }

        /* ── SEARCH BAR ── */
        .search-bar { flex: 1; max-width: 440px; }
        .search-bar form {
            display: flex;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            background: #f8fafc;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .search-bar form:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
            background: #fff;
        }
        .search-bar input {
            flex: 1;
            border: none;
            padding: 10px 16px;
            outline: none;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            background: transparent;
            color: #0f172a;
            font-weight: 400;
        }
        .search-bar input::placeholder { color: #94a3b8; font-weight: 400; }
        .search-bar button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 0 20px;
            cursor: pointer;
            font-size: 14px;
            transition: background .18s;
            flex-shrink: 0;
        }
        .search-bar button:hover { background: #1d4ed8; }

        /* ── MAIN NAV ── */
        .main-nav {
            display: flex;
            align-items: center;
            gap: 0;
            flex-shrink: 0;
        }
        .main-nav a,
        .main-nav .nav-dropdown-wrapper > span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            transition: background .16s, color .16s;
            cursor: pointer;
            white-space: nowrap;
            letter-spacing: 0;
            position: relative;
        }
        .main-nav a i, .main-nav .nav-dropdown-wrapper > span i {
            font-size: 13px; opacity: 0.6;
        }
        .main-nav a:hover,
        .main-nav .nav-dropdown-wrapper:hover > span {
            background: #f1f5f9;
            color: #1e293b;
        }
        .main-nav a:hover i, .main-nav .nav-dropdown-wrapper:hover > span i { opacity: 1; }
        .main-nav a.active {
            color: #2563eb;
            background: #dbeafe;
            font-weight: 700;
        }
        .main-nav a.active i { opacity: 1; }

        /* ── Category Dropdown ── */
        .nav-dropdown-wrapper { position: relative; }
        .nav-cat-dropdown {
            display: none;
            position: absolute;
            left: 0;
            top: calc(100% + 10px);
            background: #fff;
            min-width: 240px;
            border-radius: 16px;
            box-shadow: 0 20px 48px rgba(15,23,42,0.14), 0 4px 12px rgba(15,23,42,0.06);
            border: 1.5px solid #e8edf3;
            padding: 8px;
            z-index: 9999;
            list-style: none;
            margin: 0;
            animation: headerDropFade .18s ease;
        }
        .nav-cat-dropdown::before {
            content: '';
            position: absolute;
            top: -10px; left: 0; right: 0;
            height: 10px;
        }
        .nav-dropdown-wrapper:hover .nav-cat-dropdown { display: block; }
        .nav-cat-dropdown li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            font-size: 13.5px;
            font-weight: 500;
            color: #374151;
            text-decoration: none;
            transition: background .14s;
            border-radius: 10px;
            background: none;
        }
        .nav-cat-dropdown li a:hover { background: #f1f5f9; color: #0f172a; }
        .nav-cat-dropdown li a i { width: 18px; text-align: center; color: #94a3b8; font-size: 12px; }
        .nav-cat-dropdown li a:hover i { color: #2563eb; }

        /* ── Build PC Button (special) ── */
        .nav-buildpc-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 7px !important;
            background: #0f172a !important;
            color: #38bdf8 !important;
            border-radius: 10px !important;
            padding: 7px 14px !important;
            font-weight: 700 !important;
            font-size: 13.5px !important;
            letter-spacing: 0;
            transition: background .18s, box-shadow .18s, transform .18s !important;
            text-decoration: none !important;
            border: none !important;
        }
        .nav-buildpc-btn:hover, .nav-buildpc-btn.active {
            background: #38bdf8 !important;
            color: #0f172a !important;
            box-shadow: 0 0 20px rgba(56,189,248,.35) !important;
            transform: translateY(-1px) !important;
        }
        .nav-buildpc-badge {
            background: #38bdf8;
            color: #0f172a;
            font-size: 9px;
            font-weight: 800;
            border-radius: 5px;
            padding: 2px 6px;
            line-height: 1.4;
            letter-spacing: .04em;
        }
        .nav-buildpc-btn:hover .nav-buildpc-badge,
        .nav-buildpc-btn.active .nav-buildpc-badge {
            background: #0f172a;
            color: #38bdf8;
        }

        /* ── USER MENU ROW ── */
        .user-menu {
            display: flex;
            gap: 6px;
            align-items: center;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* ── Cart Button ── */
        .cart-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: transparent;
            color: #64748b;
            text-decoration: none;
            font-size: 17px;
            border: 1.5px solid #e2e8f0;
            transition: background .18s, border-color .18s, color .18s;
        }
        .cart-btn:hover {
            background: #dbeafe;
            border-color: #bfdbfe;
            color: #2563eb;
        }
        .cart-count {
            position: absolute;
            top: -5px; right: -5px;
            background: #ef4444;
            color: #fff;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid #fff;
            line-height: 1;
        }

        /* ── Notification Button ── */
        .notif-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: transparent;
            color: #64748b;
            font-size: 17px;
            border: 1.5px solid #e2e8f0;
            transition: background .18s, border-color .18s, color .18s;
            cursor: pointer;
        }
        .notif-btn:hover {
            background: #dbeafe;
            border-color: #bfdbfe;
            color: #2563eb;
        }
        .notif-count {
            position: absolute;
            top: -4px; right: -4px;
            background: #ef4444;
            color: #fff;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid #fff;
        }
        .notif-wrapper { position: relative; }

        /* ── Notification Dropdown ── */
        .notif-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            width: 360px;
            max-width: calc(100vw - 20px);
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 56px rgba(0,0,0,0.14), 0 4px 16px rgba(0,0,0,0.06);
            border: 1.5px solid #e8edf3;
            z-index: 10000;
            overflow: hidden;
            animation: headerDropFade .18s ease;
        }
        .notif-dropdown::before {
            content: '';
            position: absolute;
            top: -12px; left: 0; right: 0;
            height: 12px;
        }
        .notif-wrapper.open .notif-dropdown { display: block; }
        .notif-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 18px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .notif-header h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            font-family: 'Outfit', sans-serif;
        }
        .notif-read-all {
            font-size: 12px;
            color: #2563eb;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }
        .notif-read-all:hover { text-decoration: underline; }
        .notif-list { max-height: 360px; overflow-y: auto; }
        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 13px 18px;
            border-bottom: 1px solid #f8fafc;
            cursor: pointer;
            transition: background .15s;
            text-decoration: none;
        }
        .notif-item:hover { background: #f8fafc; }
        .notif-item.unread { background: #f0f7ff; }
        .notif-item.unread:hover { background: #e8f2ff; }
        .notif-icon {
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0; margin-top: 2px;
        }
        .notif-icon.order    { background: #dbeafe; color: #2563eb; }
        .notif-icon.promotion{ background: #fef3c7; color: #d97706; }
        .notif-icon.system   { background: #fee2e2; color: #dc2626; }
        .notif-icon.info     { background: #d1fae5; color: #059669; }
        .notif-body { flex: 1; min-width: 0; }
        .notif-title {
            font-size: 13px; font-weight: 600; color: #0f172a;
            margin-bottom: 3px; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis;
        }
        .notif-msg {
            font-size: 12px; color: #64748b; line-height: 1.5;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .notif-time { font-size: 11px; color: #94a3b8; margin-top: 4px; }
        .notif-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #2563eb; flex-shrink: 0; margin-top: 6px;
        }
        .notif-empty {
            text-align: center; padding: 36px 16px;
            color: #94a3b8; font-size: 13px;
        }
        .notif-empty i { font-size: 32px; display: block; margin-bottom: 10px; color: #cbd5e1; }

        /* ── Avatar / User Dropdown ── */
        .user-menu-item { position: relative; }
        .avatar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px; height: 42px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            cursor: pointer;
            background: #f1f5f9;
            transition: box-shadow .2s, border-color .2s;
            padding: 0;
            text-decoration: none !important;
        }
        .avatar-btn:hover {
            box-shadow: 0 0 0 4px rgba(37,99,235,0.15);
            border-color: #2563eb;
        }
        .avatar-btn img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-btn .fa-user-circle { font-size: 24px; color: #64748b; }

        /* User dropdown */
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            background: #ffffff;
            min-width: 240px;
            box-shadow: 0 20px 48px rgba(0,0,0,0.14), 0 4px 16px rgba(0,0,0,0.06);
            z-index: 9999;
            border-radius: 18px;
            padding: 8px;
            list-style: none;
            margin: 0;
            border: 1.5px solid #e8edf3;
            animation: headerDropFade .18s ease;
        }
        @keyframes headerDropFade {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .user-menu-item:hover .dropdown-content { display: block; }
        .dropdown-content::before {
            content: ''; position: absolute;
            top: -12px; left: 0; right: 0; height: 12px;
        }

        /* Greeting in dropdown */
        .dropdown-greeting {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 6px;
        }
        .dropdown-greeting-info small {
            display: block; font-size: 12px;
            color: #64748b; margin-bottom: 3px;
        }
        .dropdown-greeting-info strong {
            display: block; font-size: 16px;
            color: #0f172a; font-weight: 700;
            max-width: 160px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .dropdown-greeting-emoji { font-size: 30px; line-height: 1; user-select: none; }
        .dropdown-greeting-avatar {
            width: 40px; height: 40px;
            border-radius: 50%; object-fit: cover;
            border: 2px solid #bfdbfe; flex-shrink: 0;
        }
        .dropdown-greeting-avatar-icon {
            width: 40px; height: 40px; border-radius: 50%;
            background: #f0f4ff;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .dropdown-greeting-avatar-icon .fa { font-size: 20px; color: #2563eb; }

        .dropdown-content li a {
            color: #334155;
            padding: 10px 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: background .15s;
            border-radius: 10px;
        }
        .dropdown-content li a:hover { background: #f1f5f9; color: #0f172a; }
        .dropdown-content li a i { width: 18px; text-align: center; font-size: 14px; color: #64748b; }
        .dropdown-content li a:hover i { color: #2563eb; }
        .dropdown-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }
        .dropdown-content li a.logout-link { color: #ef4444; }
        .dropdown-content li a.logout-link i { color: #ef4444; }
        .dropdown-content li a.logout-link:hover { background: #fff5f5; color: #dc2626; }

        /* Header avatar small */
        .header-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            object-fit: cover; border: 2px solid #bfdbfe;
            vertical-align: middle; flex-shrink: 0;
        }
        .header-avatar-icon { font-size: 18px; color: #2563eb; vertical-align: middle; }

        /* ================================================================
           CHAT WIDGET (floating)
           ================================================================ */
        .chat-widget {
            position: fixed;
            right: 24px;
            bottom: 88px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }
        .chat-widget .chat-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            border-radius: 999px;
            background: #0f172a;
            color: #fff;
            box-shadow: 0 20px 48px rgba(15,23,42,0.25);
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            min-width: 220px;
            cursor: grab;
            user-select: none;
            border: 1.5px solid rgba(255,255,255,0.08);
        }
        .chat-widget .chat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 54px rgba(15,23,42,0.3);
            background: #1e293b;
        }
        .chat-widget .chat-card .chat-icon {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .chat-widget .chat-card .chat-icon i { color: #fff; font-size: 18px; }
        .chat-widget .chat-card .chat-label {
            font-size: 13.5px; font-weight: 600;
            line-height: 1.3; color: #e2e8f0; white-space: nowrap;
        }
        .chat-widget .chat-close {
            width: 28px; height: 28px; border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
            font-size: 18px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: none;
            transition: background 0.18s, color 0.18s;
        }
        .chat-widget .chat-close:hover { background: rgba(239,68,68,0.8); color: #fff; }

        /* Chat popup */
        .chat-popup {
            position: fixed;
            right: 24px;
            bottom: 160px;
            left: auto; top: auto;
            width: 360px;
            max-width: calc(100vw - 40px);
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 32px 80px rgba(15,23,42,0.18);
            overflow: hidden;
            display: none;
            z-index: 9999;
            border: 1.5px solid #e2e8f0;
        }
        .chat-popup.open { display: block; }
        .chat-popup .chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            background: #0f172a;
            color: #fff;
            cursor: grab;
            user-select: none;
        }
        .chat-popup .chat-header:active { cursor: grabbing; }
        .chat-popup .chat-header h3 { margin: 0; font-size: 15px; line-height: 1.3; color: #e2e8f0; }
        .chat-popup .chat-header button {
            width: 32px; height: 32px;
            border: none; border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            cursor: pointer; font-size: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            transition: background .18s;
        }
        .chat-popup .chat-header button:hover { background: rgba(239,68,68,0.8); color: #fff; }
        .chat-popup .chat-body {
            background: #f8fafc;
            padding: 16px 16px 12px;
            max-height: 400px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .chat-popup .chat-messages {
            flex: 1; min-height: 200px;
            overflow-y: auto; padding-right: 6px;
        }
        .chat-popup .chat-message {
            padding: 11px 14px;
            border-radius: 16px;
            margin-bottom: 10px;
            max-width: 82%;
            line-height: 1.55;
            background: #fff;
            box-shadow: 0 1px 4px rgba(15,23,42,0.06);
            font-size: 13.5px;
            color: #0f172a;
        }
        .chat-popup .chat-message.admin {
            margin-left: auto;
            background: #dbeafe;
            color: #1e40af;
        }
        .chat-popup .chat-message .sender {
            display: block; font-size: 11px;
            color: #64748b; margin-bottom: 4px; font-weight: 600;
        }
        .chat-popup .chat-footer { display: flex; gap: 8px; }
        .chat-popup .chat-footer input {
            flex: 1; padding: 12px 14px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #fff; font-size: 13.5px;
            color: #0f172a; font-family: 'Outfit', sans-serif;
            transition: border-color .18s, box-shadow .18s;
            outline: none;
        }
        .chat-popup .chat-footer input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        .chat-popup .chat-footer button {
            padding: 12px 16px; border: none;
            border-radius: 12px;
            background: #2563eb; color: #fff;
            font-weight: 700; cursor: pointer;
            font-family: 'Outfit', sans-serif;
            transition: background .18s;
        }
        .chat-popup .chat-footer button:hover { background: #1d4ed8; }
        .chat-popup .chat-footer button:disabled { opacity: 0.6; cursor: default; }

        /* Chat product card */
        .chat-product-ref {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 4px;
            text-decoration: none;
            transition: box-shadow .2s;
        }
        .chat-product-ref:hover { box-shadow: 0 4px 16px rgba(37,99,235,0.12); border-color: #bfdbfe; }
        .chat-product-ref .cp-img {
            width: 44px; height: 44px; border-radius: 8px;
            object-fit: cover; flex-shrink: 0;
            border: 1px solid #e2e8f0; background: #fff;
        }
        .chat-product-ref .cp-img-placeholder {
            width: 44px; height: 44px; border-radius: 8px;
            background: #dbeafe;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 20px;
        }
        .chat-product-ref .cp-info { flex: 1; min-width: 0; }
        .chat-product-ref .cp-label {
            font-size: 10px; color: #2563eb; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;
        }
        .chat-product-ref .cp-name {
            font-size: 12.5px; font-weight: 700; color: #0f172a;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .chat-product-ref .cp-price { font-size: 12px; color: #2563eb; font-weight: 700; margin-top: 2px; }

        /* Msg product card bubble */
        .msg-product-card {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.9); border: 1.5px solid #e2e8f0;
            border-radius: 10px; padding: 8px 10px; margin-bottom: 8px;
            text-decoration: none; cursor: pointer;
        }
        .msg-product-card .mpc-img {
            width: 36px; height: 36px; border-radius: 6px;
            object-fit: cover; flex-shrink: 0; background: #fff;
        }
        .msg-product-card .mpc-info .mpc-label {
            font-size: 9px; color: #2563eb; font-weight: 700; text-transform: uppercase;
        }
        .msg-product-card .mpc-info .mpc-name {
            font-size: 11.5px; font-weight: 700; color: #0f172a;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px;
        }
        .msg-product-card .mpc-info .mpc-price { font-size: 11px; color: #2563eb; font-weight: 700; }

        /* ================================================================
           BUILD PC MODAL
           ================================================================ */
        .bpc-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.6);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .bpc-overlay.open { display: flex; }
        .bpc-modal-box {
            background: #f8fafc;
            width: 960px;
            max-width: calc(100vw - 24px);
            height: 88vh;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 40px 100px rgba(0,0,0,.3);
            display: flex;
            flex-direction: column;
            position: relative;
            animation: bpcIn .22s ease;
        }
        @keyframes bpcIn {
            from { opacity:0; transform:scale(.96) translateY(14px); }
            to   { opacity:1; transform:scale(1)  translateY(0); }
        }
        .bpc-modal-close-btn {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 100000;
            width: 38px; height: 38px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,0.2);
            background: rgba(15,23,42,.85);
            backdrop-filter: blur(8px);
            color: rgba(255,255,255,0.8);
            font-size: 20px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            line-height: 1;
            box-shadow: 0 4px 20px rgba(0,0,0,.3);
            transition: background .18s, transform .18s, color .18s;
        }
        .bpc-modal-close-btn:hover { background: #ef4444; color: #fff; transform: scale(1.1); }
        .bpc-modal-box iframe { flex: 1; width: 100%; border: none; }

        /* ================================================================
           HAMBURGER + MOBILE NAV
           ================================================================ */
        .hamburger-btn {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 42px; height: 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: transparent;
            cursor: pointer;
            flex-shrink: 0;
            padding: 0;
            transition: background .18s, border-color .18s;
        }
        .hamburger-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }
        .hamburger-btn span {
            display: block; width: 18px; height: 2px;
            background: #64748b; border-radius: 2px;
            transition: transform .22s, opacity .22s;
        }
        .hamburger-btn.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .hamburger-btn.open span:nth-child(2) { opacity: 0; }
        .hamburger-btn.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* Mobile overlay + drawer */
        .mobile-nav-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,0.5); z-index: 9998;
            backdrop-filter: blur(2px);
        }
        .mobile-nav-overlay.open { display: block; }
        .mobile-nav-drawer {
            position: fixed; top: 0; right: -300px; width: 288px;
            height: 100%; background: #fff; z-index: 9999;
            box-shadow: -8px 0 40px rgba(15,23,42,0.18);
            display: flex; flex-direction: column;
            transition: right .25s cubic-bezier(.4,0,.2,1);
            overflow-y: auto;
        }
        .mobile-nav-drawer.open { right: 0; }
        .mobile-nav-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 20px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0;
        }
        .mobile-nav-header strong { font-size: 15px; color: #0f172a; font-weight: 700; }
        .mobile-nav-close {
            width: 34px; height: 34px; border-radius: 50%;
            border: none; background: #f1f5f9; color: #475569;
            font-size: 18px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background .18s, color .18s;
        }
        .mobile-nav-close:hover { background: #fee2e2; color: #ef4444; }
        .mobile-nav-links { flex: 1; padding: 8px 0; }
        .mobile-nav-links a {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 20px; font-size: 14.5px; font-weight: 600;
            color: #334155; text-decoration: none;
            border-bottom: 1px solid #f8fafc;
            transition: background .14s, color .14s;
        }
        .mobile-nav-links a i { width: 18px; text-align: center; color: #94a3b8; transition: color .14s; }
        .mobile-nav-links a:hover { background: #f1f5f9; color: #0f172a; }
        .mobile-nav-links a:hover i { color: #2563eb; }
        .mobile-nav-links a.mobile-buildpc {
            background: #0f172a; color: #38bdf8;
            border-radius: 12px; margin: 10px 14px; padding: 13px 16px;
            border-bottom: none;
        }
        .mobile-nav-links a.mobile-buildpc i { color: #38bdf8; }
        .mobile-nav-cat-label {
            padding: 12px 20px 4px;
            font-size: 10px; font-weight: 700;
            color: #94a3b8; text-transform: uppercase; letter-spacing: .1em;
        }

        /* ── Responsive Breakpoints ── */
        @media (max-width: 960px) {
            .main-nav { display: none !important; }
            .hamburger-btn { display: flex !important; }
            .search-bar { max-width: none !important; flex: 1; }
        }
        @media (max-width: 600px) {
            .header-inner { gap: 10px; }
            .logo-text { font-size: 18px; }
        }
        @media (max-width: 420px) {
            .top-nav .container { padding: 0 12px; }
            .top-nav .top-nav-left { display: none; }
        }
    </style>



    <script src="<?php echo AssetHelper::url('public/js/Jquery/Jquery.min.js'); ?>"></script>



    <?php if (isset($_SESSION['user'])): ?>
    <!-- VAPID public key cho Web Push Notifications -->
    <script>window.VAPID_PUBLIC_KEY = <?php echo json_encode(VAPID_PUBLIC_KEY); ?>;</script>
    <script>
    (function() {
        var BASE_URL = '<?php echo BASE_URL; ?>';

        var notifIcons = {
            order:     'fa-shopping-bag',
            promotion: 'fa-tag',
            system:    'fa-exclamation-circle',
            info:      'fa-info-circle'
        };

        function renderNotifications(data) {
            var list    = document.getElementById('notifList');
            var badge   = document.getElementById('notifCount');
            if (!list) return;

            // Cập nhật badge
            if (data.unread > 0) {
                badge.textContent = data.unread > 99 ? '99+' : data.unread;
                badge.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
            }

            if (!data.notifications || data.notifications.length === 0) {
                list.innerHTML = '<div class="notif-empty"><i class="fa fa-bell-slash"></i>Chưa có thông báo</div>';
                return;
            }

            var html = '';
            data.notifications.forEach(function(n) {
                var icon    = notifIcons[n.type] || 'fa-info-circle';
                var unread  = !n.is_read ? 'unread' : '';
                var dot     = !n.is_read ? '<span class="notif-dot"></span>' : '';
                var href    = n.link ? n.link : 'javascript:void(0)';

                html += '<a href="' + href + '" class="notif-item ' + unread + '" data-id="' + n.id + '">'
                      + '  <div class="notif-icon ' + n.type + '"><i class="fa ' + icon + '"></i></div>'
                      + '  <div class="notif-body">'
                      + '    <div class="notif-title">' + escapeHtml(n.title) + '</div>'
                      + '    <div class="notif-msg">'  + escapeHtml(n.message) + '</div>'
                      + '    <div class="notif-time">' + n.time_ago + '</div>'
                      + '  </div>'
                      + dot
                      + '</a>';
            });
            list.innerHTML = html;

            // Click từng thông báo → đánh dấu đã đọc
            list.querySelectorAll('.notif-item[data-id]').forEach(function(el) {
                el.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    this.classList.remove('unread');
                    var dot = this.querySelector('.notif-dot');
                    if (dot) dot.remove();
                    $.post(BASE_URL + 'notification.php', { action: 'read', id: id });
                    // Giảm badge
                    var b = document.getElementById('notifCount');
                    var cur = parseInt(b.textContent) || 0;
                    if (cur > 1) { b.textContent = cur - 1; }
                    else { b.style.display = 'none'; }
                });
            });
        }

        function escapeHtml(text) {
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(text));
            return d.innerHTML;
        }

        function loadNotifications() {
            $.get(BASE_URL + 'notification.php?action=get', function(res) {
                if (res && res.success) renderNotifications(res);
            }, 'json').fail(function(){});
        }

        document.addEventListener('DOMContentLoaded', function() {
            var wrapper  = document.getElementById('notifWrapper');
            var dropdown = document.getElementById('notifDropdown');
            var readAll  = document.getElementById('notifReadAll');

            if (!wrapper) return;

            // Toggle dropdown khi click vào chuông
            document.getElementById('notifBtn').addEventListener('click', function(e) {
                e.stopPropagation();
                wrapper.classList.toggle('open');
                if (wrapper.classList.contains('open')) loadNotifications();
            });

            // Đóng khi click ra ngoài
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    wrapper.classList.remove('open');
                }
            });

            // Đánh dấu tất cả đã đọc
            if (readAll) {
                readAll.addEventListener('click', function(e) {
                    e.stopPropagation();
                    $.post(BASE_URL + 'notification.php', { action: 'read_all' }, function() {
                        document.querySelectorAll('.notif-item.unread').forEach(function(el) {
                            el.classList.remove('unread');
                            var dot = el.querySelector('.notif-dot');
                            if (dot) dot.remove();
                        });
                        var badge = document.getElementById('notifCount');
                        if (badge) badge.style.display = 'none';
                    });
                });
            }

            // Load lần đầu + auto-refresh mỗi 30 giây
            loadNotifications();
            setInterval(function() {
                if (!wrapper.classList.contains('open')) {
                    $.get(BASE_URL + 'notification.php?action=count', function(res) {
                        if (!res || !res.success) return;
                        var badge = document.getElementById('notifCount');
                        if (res.unread > 0) {
                            badge.textContent = res.unread > 99 ? '99+' : res.unread;
                            badge.style.display = 'inline-flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }, 'json').fail(function(){});
                }
            }, 30000);
        });
    })();
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="top-nav">
        <div class="container">
            <span class="top-nav-left">
                <i class="fa fa-phone"></i>
                Hotline: <strong>1800 6975</strong>
            </span>
            <div class="top-nav-right">
                <a href="<?php echo BASE_URL; ?>tintuc.php"><i class="fa fa-newspaper-o"></i> Tin tức</a>
                <a href="<?php echo BASE_URL; ?>lienhe.php"><i class="fa fa-envelope-o"></i> Liên hệ</a>
                <a href="<?php echo BASE_URL; ?>gioithieu.php"><i class="fa fa-info-circle"></i> Giới thiệu</a>
            </div>
        </div>
    </div>

    <header>
        <div class="container header-inner">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>index.php">
                    <span class="logo-icon"><i class="fa fa-microchip"></i></span>
                    <span class="logo-text">PC<span>Store</span></span>
                </a>
            </div>

            <!-- Main Nav -->
            <?php
                $currentPage = basename($_SERVER['PHP_SELF']);
                // Lấy danh mục từ DB cho dropdown — dùng static variable để cache trong 1 request
                // Sử dụng global $db để tránh tạo connection mới trong view
                if (!isset($navCategories) || !is_array($navCategories)) {
                    $navCategories = [];
                    if (isset($db) && $db instanceof PDO) {
                        try {
                            $navStmt = $db->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 12");
                            $navCategories = $navStmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            Logger::warning('Failed to load nav categories', ['error' => $e->getMessage()]);
                        }
                    }
                }
            ?>
            <nav class="main-nav">
                <a href="<?php echo BASE_URL; ?>index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                    <i class="fa fa-home"></i> Trang chủ
                </a>
                <a href="<?php echo BASE_URL; ?>lienhe.php" class="<?php echo $currentPage === 'lienhe.php' ? 'active' : ''; ?>">
                    <i class="fa fa-headphones"></i> Hỗ trợ
                </a>
                <a href="<?php echo BASE_URL; ?>buildpc_modal.php"
                   class="nav-buildpc-btn <?php echo ($currentPage === 'buildpc_modal.php' || $currentPage === 'buildpc.php') ? 'active' : ''; ?>"
                   id="navBuildPcBtn"
                   title="Tự build cấu hình PC theo ý bạn">
                    <i class="fa fa-microchip"></i>
                    <span>Build PC</span>
                    <span class="nav-buildpc-badge">MỚI</span>
                </a>

                <div class="nav-dropdown-wrapper">
                    <span>
                        <i class="fa fa-th-large"></i> Danh mục
                        <i class="fa fa-caret-down" style="font-size:11px;"></i>
                    </span>
                    <ul class="nav-cat-dropdown">
                        <?php if (!empty($navCategories)): ?>
                            <?php foreach ($navCategories as $cat): ?>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>index.php?category_id=<?php echo $cat['id']; ?>">
                                        <i class="fa fa-circle-o"></i>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>index.php"><i class="fa fa-th"></i> Tất cả sản phẩm</a></li>
                        <?php endif; ?>
                        <li style="border-top:1px solid #f1f5f9;margin-top:4px;padding-top:4px;">
                            <a href="<?php echo BASE_URL; ?>index.php" style="color:#2563eb;font-weight:600;">
                                <i class="fa fa-arrow-right"></i> Xem tất cả
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="search-bar">
                <form action="<?php echo BASE_URL; ?>search.php" method="GET">
                    <input type="text" name="key"
                           value="<?php echo htmlspecialchars($_GET['key'] ?? ''); ?>"
                           placeholder="Tìm kiếm linh kiện...">
                    <button type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>

            <!-- Hamburger button (mobile) -->
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu" type="button">
                <span></span><span></span><span></span>
            </button>

            <div class="user-menu">
                <!-- Thông báo -->
                <?php if (isset($_SESSION['user'])): ?>
                <div class="notif-wrapper" id="notifWrapper">
                    <button class="notif-btn" id="notifBtn" title="Thông báo">
                        <i class="fa fa-bell"></i>
                        <span class="notif-count" id="notifCount" style="display:none"></span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h4>🔔 Thông báo</h4>
                            <button class="notif-read-all" id="notifReadAll">Đánh dấu tất cả đã đọc</button>
                        </div>
                        <div class="notif-list" id="notifList">
                            <div class="notif-empty"><i class="fa fa-bell-slash"></i>Chưa có thông báo</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Giỏ hàng -->
                <a href="<?php echo BASE_URL; ?>giohang.php" class="cart-btn" title="Giỏ hàng">
                    <i class="fa fa-shopping-cart"></i>
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>

                <!-- Avatar / Tài khoản -->
                <div class="user-menu-item">
                    <a href="javascript:void(0)" class="avatar-btn" title="Tài khoản">
                        <?php if (isset($_SESSION['user']) && !empty($_SESSION['user']['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar']); ?>" alt="Avatar">
                        <?php else: ?>
                            <i class="fa fa-user-circle"></i>
                        <?php endif; ?>
                    </a>

                    <ul class="dropdown-content">
                        <?php if (isset($_SESSION['user'])): ?>
                            <!-- Greeting -->
                            <?php
                                $hour = (int)date('H');
                                if ($hour >= 5 && $hour < 12) {
                                    $greetTime = 'buổi sáng';
                                    $greetIcon = '☀️';
                                } elseif ($hour >= 12 && $hour < 18) {
                                    $greetTime = 'buổi chiều';
                                    $greetIcon = '🌞';
                                } else {
                                    $greetTime = 'buổi tối';
                                    $greetIcon = '🌙';
                                }
                            ?>
                            <li style="list-style:none">
                                <div class="dropdown-greeting">
                                    <div class="dropdown-greeting-info">
                                        <small>Xin chào <?php echo $greetTime; ?>,</small>
                                        <strong><?php echo htmlspecialchars($_SESSION['user']['fullname']); ?></strong>
                                    </div>
                                    <span class="dropdown-greeting-emoji"><?php echo $greetIcon; ?></span>
                                </div>
                            </li>
                            <li><a href="<?php echo BASE_URL; ?>thongtin.php"><i class="fa fa-user"></i> Thông tin cá nhân</a></li>
                            <li><a href="<?php echo BASE_URL; ?>lichsu.php"><i class="fa fa-history"></i> Lịch sử đơn hàng</a></li>
                            <li><a href="<?php echo BASE_URL; ?>loyalty.php"><i class="fa fa-star" style="color:#f59e0b;"></i> Điểm tích lũy</a></li>
                            <li><a href="<?php echo BASE_URL; ?>doimatkhau.php"><i class="fa fa-lock"></i> Đổi mật khẩu</a></li>
                            <div class="dropdown-divider"></div>
                            <li><a href="<?php echo BASE_URL; ?>dangxuat.php" class="logout-link"><i class="fa fa-sign-out"></i> Đăng xuất</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>taikhoan.php"><i class="fa fa-sign-in"></i> Đăng nhập / Đăng ký</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

    </header>

    <!-- Mobile Nav Overlay -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>

    <!-- Mobile Nav Drawer -->
    <div class="mobile-nav-drawer" id="mobileNavDrawer">
        <div class="mobile-nav-header">
            <strong>&#9776; Menu</strong>
            <button class="mobile-nav-close" id="mobileNavClose">&times;</button>
        </div>
        <div class="mobile-nav-links">
            <?php $currentPage = $currentPage ?? basename($_SERVER['PHP_SELF']); ?>
            <a href="<?php echo BASE_URL; ?>index.php"><i class="fa fa-home"></i> Trang ch&#7911;</a>
            <a href="<?php echo BASE_URL; ?>lienhe.php"><i class="fa fa-headphones"></i> H&#7895; tr&#7907;</a>
            <a href="<?php echo BASE_URL; ?>buildpc_modal.php" class="mobile-buildpc"><i class="fa fa-microchip"></i> Build PC</a>
            <?php if (!empty($navCategories)): ?>
            <div class="mobile-nav-cat-label">Danh m&#7909;c</div>
            <?php foreach ($navCategories as $cat): ?>
            <a href="<?php echo BASE_URL; ?>index.php?category_id=<?php echo $cat['id']; ?>">
                <i class="fa fa-circle-o"></i> <?php echo htmlspecialchars($cat['name']); ?>
            </a>
            <?php endforeach; endif; ?>
            <?php if (isset($_SESSION['user'])): ?>
            <div class="mobile-nav-cat-label">T&#224;i kho&#7843;n</div>
            <a href="<?php echo BASE_URL; ?>thongtin.php"><i class="fa fa-user"></i> H&#7891; s&#417;</a>
            <a href="<?php echo BASE_URL; ?>lichsu.php"><i class="fa fa-history"></i> &#272;&#417;n h&#224;ng</a>
            <a href="<?php echo BASE_URL; ?>dangxuat.php" style="color:#ef4444;"><i class="fa fa-sign-out" style="color:#ef4444;"></i> &#272;&#259;ng xu&#7845;t</a>
            <?php else: ?>
            <a href="<?php echo BASE_URL; ?>taikhoan.php"><i class="fa fa-sign-in"></i> &#272;&#259;ng nh&#7853;p / &#272;&#259;ng k&#253;</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function(){
        var btn     = document.getElementById('hamburgerBtn');
        var drawer  = document.getElementById('mobileNavDrawer');
        var overlay = document.getElementById('mobileNavOverlay');
        var closeBtn = document.getElementById('mobileNavClose');
        if (!btn || !drawer || !overlay) return;
        function openMenu() { btn.classList.add('open'); drawer.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeMenu() { btn.classList.remove('open'); drawer.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow = ''; }
        btn.addEventListener('click', function() { drawer.classList.contains('open') ? closeMenu() : openMenu(); });
        if (closeBtn) closeBtn.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
    })();
    </script>


    <div class="chat-widget" id="customerChatWidget">
        <button class="chat-close" id="closeChatWidget" type="button">×</button>
        <button class="chat-card" id="openChatPopup" type="button">
            <span class="chat-icon"><i class="fa fa-comments"></i></span>
            <span class="chat-label">Bạn cần hỗ trợ gì?</span>
        </button>
    </div>

    <div class="chat-popup" id="customerChatPopup">
        <div class="chat-header">
            <h3>Chat hỗ trợ</h3>
            <button id="closeChatPopup" type="button">×</button>
        </div>
        <div class="chat-body">
            <div class="chat-messages" id="popupChatMessages"></div>
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Product Card Banner - hiển thị khi đang xem sản phẩm -->
                <div id="chatProductRefBanner" style="display:none; padding: 0 0 8px 0;">
                    <a id="chatProductRefLink" href="#" class="chat-product-ref" target="_blank">
                        <div id="chatProductRefImgWrap">
                            <div class="cp-img-placeholder" id="chatProductRefPlaceholder">📦</div>
                            <img id="chatProductRefImg" class="cp-img" src="" alt="" style="display:none;">
                        </div>
                        <div class="cp-info">
                            <div class="cp-label">📌 Sản phẩm đang xem</div>
                            <div class="cp-name" id="chatProductRefName"></div>
                            <div class="cp-price" id="chatProductRefPrice"></div>
                        </div>
                        <div style="font-size:18px;color:#f59e0b;flex-shrink:0;">&#128279;</div>
                    </a>
                    <div style="text-align:center; font-size:11px; color:#94a3b8; margin-top:4px;">
                        Sản phẩm này sẽ được gắn kèm vào tin nhắn đầu tiên của bạn
                    </div>
                </div>
                <div class="chat-footer">
                    <input type="text" id="popupChatInput" placeholder="Nhập tin nhắn...">
                    <button id="popupChatSend">Gửi</button>
                </div>
            <?php else: ?>
                <div style="padding: 14px 0; text-align: center; color: #475569;">
                    Vui lòng <a href="<?php echo BASE_URL; ?>taikhoan.php" style="color:#ec4899; font-weight:700;">đăng nhập</a> để chat.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var openBtn = document.getElementById('openChatPopup');
            var closeBtn = document.getElementById('closeChatPopup');
            var popup = document.getElementById('customerChatPopup');
            var widgetClose = document.getElementById('closeChatWidget');
            var widget = document.getElementById('customerChatWidget');

            if (openBtn && popup) {
                openBtn.addEventListener('click', function() {
                    popup.classList.toggle('open');
                    if (popup.classList.contains('open')) {
                        positionPopup();
                        loadPopupMessages();
                        updateProductBanner();
                    }
                });
            }
            if (closeBtn && popup) {
                closeBtn.addEventListener('click', function() {
                    popup.classList.remove('open');
                });
            }
            if (widgetClose && widget) {
                widgetClose.addEventListener('click', function() {
                    widget.style.display = 'none';
                    popup.classList.remove('open');
                });
            }

            var dragHandle = widget ? widget.querySelector('.chat-card') : null;
            if (dragHandle && widget) {
                var isDragging = false;
                var dragStartX = 0;
                var dragStartY = 0;
                var widgetStartLeft = 0;
                var widgetStartTop = 0;
                var moved = false;

                dragHandle.addEventListener('mousedown', function(e) {
                    if (e.target.closest('#closeChatWidget')) return;
                    e.preventDefault();
                    var rect = widget.getBoundingClientRect();
                    widget.style.left = rect.left + 'px';
                    widget.style.top = rect.top + 'px';
                    widget.style.right = 'auto';
                    widget.style.bottom = 'auto';
                    isDragging = true;
                    moved = false;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;
                    widgetStartLeft = rect.left;
                    widgetStartTop = rect.top;
                    document.body.style.userSelect = 'none';
                });

                document.addEventListener('mousemove', function(e) {
                    if (!isDragging) return;
                    var deltaX = e.clientX - dragStartX;
                    var deltaY = e.clientY - dragStartY;
                    var newLeft = Math.max(10, Math.min(window.innerWidth - widget.offsetWidth - 10, widgetStartLeft + deltaX));
                    var newTop = Math.max(10, Math.min(window.innerHeight - widget.offsetHeight - 10, widgetStartTop + deltaY));
                    widget.style.left = newLeft + 'px';
                    widget.style.top = newTop + 'px';
                    moved = moved || Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3;
                    if (popup.classList.contains('open')) {
                        positionPopup();
                    }
                });

                document.addEventListener('mouseup', function() {
                    if (!isDragging) return;
                    isDragging = false;
                    document.body.style.userSelect = '';
                });

                dragHandle.addEventListener('click', function(e) {
                    if (moved) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
            }

            function positionPopup() {
                if (!popup || !widget) return;
                var rect = widget.getBoundingClientRect();
                var popupHeight = popup.offsetHeight || 400;
                var top = rect.top - popupHeight - 10;
                if (top < 10) {
                    top = rect.top + rect.height + 10;
                }
                var left = rect.left;
                if (left + popup.offsetWidth > window.innerWidth - 10) {
                    left = Math.max(10, window.innerWidth - popup.offsetWidth - 10);
                }
                popup.style.left = left + 'px';
                popup.style.top = top + 'px';
                popup.style.right = 'auto';
                popup.style.bottom = 'auto';
            }

            var sendButton = document.getElementById('popupChatSend');
            var messageInput = document.getElementById('popupChatInput');
            if (sendButton && messageInput) {
                sendButton.addEventListener('click', function() {
                    sendPopupMessage();
                });
                messageInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        sendPopupMessage();
                    }
                });
            }

            function loadPopupMessages() {
                $.get('<?php echo BASE_URL; ?>chat.php?action=get', function(response) {
                    if (response.success) {
                        displayPopupMessages(response.messages);
                    }
                }, 'json');
            }

            function renderProductCard(ref, forBubble) {
                // Parse product ref từ message
                var imgHtml = '';
                if (ref.image) {
                    imgHtml = '<img class="' + (forBubble ? 'mpc-img' : 'cp-img') + '" src="' + escHtml(ref.image) + '" alt="" onerror="this.style.display=\'none\'">';
                } else {
                    imgHtml = '<div style="' + (forBubble ? 'width:36px;height:36px;' : 'width:44px;height:44px;') + 'border-radius:6px;background:#fde68a;display:flex;align-items:center;justify-content:center;font-size:' + (forBubble ? '16' : '20') + 'px;flex-shrink:0;">\uD83D\uDCE6</div>';
                }
                if (forBubble) {
                    return '<a href="' + escHtml(ref.url) + '" class="msg-product-card" target="_blank">'
                        + imgHtml
                        + '<div class="mpc-info">'
                        + '<div class="mpc-label">\uD83D\uDCCC S\u1EA3n ph\u1EA9m li\u00ean quan</div>'
                        + '<div class="mpc-name">' + escHtml(ref.name) + '</div>'
                        + '<div class="mpc-price">' + escHtml(ref.price) + '</div>'
                        + '</div></a>';
                } else {
                    return '<a href="' + escHtml(ref.url) + '" class="chat-product-ref" target="_blank">'
                        + imgHtml
                        + '<div class="cp-info">'
                        + '<div class="cp-label">\uD83D\uDCCC S\u1EA3n ph\u1EA9m \u0111ang xem</div>'
                        + '<div class="cp-name">' + escHtml(ref.name) + '</div>'
                        + '<div class="cp-price">' + escHtml(ref.price) + '</div>'
                        + '</div>&#128279;</a>';
                }
            }

            function parseMessageWithRef(rawMessage) {
                // T\u00ecm [PRODUCT_REF:{...}] \u1edf \u0111\u1ea7u tin nh\u1eafn
                var refMatch = rawMessage.match(/^\[PRODUCT_REF:(\{[^\]]+\})\]\n?/);
                if (!refMatch) return { ref: null, text: rawMessage };
                try {
                    var ref = JSON.parse(refMatch[1]);
                    var text = rawMessage.substring(refMatch[0].length);
                    return { ref: ref, text: text };
                } catch(e) {
                    return { ref: null, text: rawMessage };
                }
            }

            function escHtml(t) {
                if (!t) return '';
                return String(t).replace(/[&<>"']/g, function(m){
                    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
                });
            }

            function displayPopupMessages(messages) {
                var chatMessages = document.getElementById('popupChatMessages');
                if (!chatMessages) return;
                chatMessages.innerHTML = '';
                messages.forEach(function(msg) {
                    var parsed = parseMessageWithRef(msg.message);
                    var msgEl = document.createElement('div');
                    msgEl.className = 'chat-message ' + (msg.is_admin_reply ? 'admin' : 'user');
                    var sender = msg.is_admin_reply ? 'Admin User' : '<?php echo isset($_SESSION["user"]) ? addslashes($_SESSION["user"]["fullname"]) : "Bạn"; ?>';
                    var productHtml = '';
                    if (parsed.ref) {
                        productHtml = renderProductCard(parsed.ref, true);
                    }
                    msgEl.innerHTML = '<span class="sender">' + escHtml(sender) + '</span>'
                        + productHtml
                        + '<span>' + escHtml(parsed.text) + '</span>';
                    chatMessages.appendChild(msgEl);
                });
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            var _productRefSent = false; // \u0111\u00e1nh d\u1ea5u \u0111\u00e3 g\u1eedi s\u1ea3n ph\u1ea9m ref ch\u01b0a

            function sendPopupMessage() {
                if (!messageInput || !messageInput.value.trim()) return;
                var message = messageInput.value.trim();
                sendButton.disabled = true;

                // G\u1eafn th\u00f4ng tin s\u1ea3n ph\u1ea9m n\u1ebfu ch\u01b0a g\u1eedi l\u1ea7n n\u00e0o v\u00e0 \u0111ang c\u00f3 s\u1ea3n ph\u1ea9m
                var payload = { action: 'send', message: message };
                if (!_productRefSent && window.currentProduct && window.currentProduct.id) {
                    var ref = JSON.stringify({
                        id:    window.currentProduct.id,
                        name:  window.currentProduct.name,
                        price: window.currentProduct.price,
                        url:   window.currentProduct.url,
                        image: window.currentProduct.image
                    });
                    payload.product_ref = ref;
                    _productRefSent = true;
                }

                $.post('<?php echo BASE_URL; ?>chat.php', payload, function(response) {
                    sendButton.disabled = false;
                    if (response.success) {
                        messageInput.value = '';
                        loadPopupMessages();
                        // Ẩn banner sau khi g\u1eedi
                        var banner = document.getElementById('chatProductRefBanner');
                        if (banner) banner.style.display = 'none';
                    }
                }, 'json');
            }

            // Khi m\u1edf chat popup, c\u1eadp nh\u1eadt product banner n\u1ebfu \u0111ang xem s\u1ea3n ph\u1ea9m
            function updateProductBanner() {
                var banner = document.getElementById('chatProductRefBanner');
                if (!banner) return;
                if (window.currentProduct && window.currentProduct.id && !_productRefSent) {
                    banner.style.display = 'block';
                    document.getElementById('chatProductRefName').textContent = window.currentProduct.name;
                    document.getElementById('chatProductRefPrice').textContent = window.currentProduct.price;
                    document.getElementById('chatProductRefLink').href = window.currentProduct.url;
                    // Ảnh
                    var img = document.getElementById('chatProductRefImg');
                    var placeholder = document.getElementById('chatProductRefPlaceholder');
                    if (window.currentProduct.image) {
                        img.src = window.currentProduct.image;
                        img.style.display = 'block';
                        if (placeholder) placeholder.style.display = 'none';
                    }
                } else {
                    banner.style.display = 'none';
                }
            }

            // Auto-refresh popup messages mỗi 3 giây khi popup mở
            setInterval(function() {
                if (popup && popup.classList.contains('open')) {
                    loadPopupMessages();
                }
            }, 3000);
        });
    </script>

    <script>
        // Ẩn chat widget nếu không phải trang sản phẩm
        document.addEventListener('DOMContentLoaded', function() {
            var allowedPages = ['/', 'index.php', 'chitietsanpham.php', 'search.php', 'buildpc.php'];
            var currentPath = window.location.pathname;
            var currentPage = window.location.pathname.split('/').pop() || 'index.php';
            
            // Kiểm tra xem có phải trang được phép không
            var isAllowed = allowedPages.some(function(page) {
                if (page === '/') {
                    return currentPath === '/' || currentPath === '/index.php' || currentPath.endsWith('index.php');
                }
                return currentPath.endsWith(page) || currentPage === page;
            });
            
            // Nếu không phải trang được phép, ẩn chat widget
            if (!isAllowed) {
                var widget = document.getElementById('customerChatWidget');
                var popup = document.getElementById('customerChatPopup');
                if (widget) widget.style.display = 'none';
                if (popup) popup.style.display = 'none';
            }

            // Close button handler
            var closeBtn = document.getElementById('closeChatWidget');
            var widget = document.getElementById('customerChatWidget');
            if (closeBtn && widget) {
                closeBtn.addEventListener('click', function() {
                    widget.style.display = 'none';
                });
            }
        });
    </script>