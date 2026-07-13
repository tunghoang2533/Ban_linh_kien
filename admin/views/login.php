<?php
require_once dirname(dirname(__FILE__)) . '/session_check.php';

$projectRoot = dirname(dirname(dirname(__FILE__)));
require_once $projectRoot . '/config.php';
require_once $projectRoot . '/core/Database.php';
// UserModel, CsrfHelper, RateLimiter autoloaded via PSR-4 + class_alias

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try { CsrfHelper::verify(); } catch (Exception $e) { $error = $e->getMessage(); }
    if (!$error) {

    // â”€â”€ Rate limiting â€” cháº·n brute-force admin login â”€â”€
    $rlKey = RateLimiter::ipKey('admin_login');
    try {
        RateLimiter::check($rlKey);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    if (!$error) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Vui lÃ²ng nháº­p Ä‘áº§y Ä‘á»§ tÃªn Ä‘Äƒng nháº­p vÃ  máº­t kháº©u.';
    } else {
        // XÃ¡c thá»±c qua database â€” khÃ´ng cÃ²n hardcode
        $db        = Database::getInstance();
        $userModel = new UserModel($db);
        $user      = $userModel->login($username, $password);

        if ($user && (int)($user['is_admin'] ?? 0) === 1) {
            // ÄÄƒng nháº­p admin há»£p lá»‡ â€” xÃ³a attempt
            RateLimiter::reset($rlKey);

            // Chá»‘ng session fixation: táº¡o session ID má»›i sau login
            session_regenerate_id(true);

            $_SESSION['user_id']        = $user['id'];
            $_SESSION['admin_id']       = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['is_admin']       = 1;
            $_SESSION['full_name']      = $user['fullname'] ?? $user['full_name'] ?? $user['username'];
            $_SESSION['last_activity']  = time();
            header('Location: ../index.php');
            exit;
        } else {
            // Sai â€” ghi nháº­n attempt
            try {
                RateLimiter::record($rlKey);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if (!$error) {
                $remaining = RateLimiter::remainingAttempts($rlKey);
                $error = 'TÃªn Ä‘Äƒng nháº­p hoáº·c máº­t kháº©u khÃ´ng chÃ­nh xÃ¡c, hoáº·c tÃ i khoáº£n khÃ´ng cÃ³ quyá»n admin.'
                    . ($remaining > 0 ? " (cÃ²n {$remaining} láº§n thá»­)" : '');
            }
        }
    }
    } // end rate limit check
    } // end if (!$error) CSRF
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ÄÄƒng nháº­p Admin - <?php echo SITE_NAME ?? 'Ban Linh Kiá»‡n'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-base:     #f4f4f5;
            --bg-surface:  #ffffff;
            --bg-elevated: #f4f4f5;
            --border:      rgba(0,0,0,0.09);
            --border-mid:  rgba(0,0,0,0.14);
            --text-1:      #09090b;
            --text-2:      #52525b;
            --text-muted:  #71717a;
            --accent:      #6366f1;
            --accent-glow: rgba(99,102,241,0.18);
            --danger:      #dc2626;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-base);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Subtle grid background */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,0,0,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.05) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* Glow orb */
        body::after {
            content: '';
            position: absolute;
            width: 480px; height: 480px;
            background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 400px;
            padding: 24px;
            animation: slideUp 0.4s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Brand mark */
        .login-brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-mark {
            width: 52px; height: 52px;
            background: var(--accent);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: white;
            margin: 0 auto 14px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-1);
            letter-spacing: -0.025em;
        }

        .brand-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 3px;
            font-weight: 400;
        }

        /* Card */
        .login-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 28px;
        }

        /* Error box */
        .error-box {
            background: rgba(239,68,68,0.10);
            border: 1px solid rgba(239,68,68,0.25);
            color: #dc2626;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
            line-height: 1.5;
        }

        /* Form */
        .form-group { margin-bottom: 16px; }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 7px;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 13px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 13px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 11px 13px 11px 38px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-mid);
            border-radius: 10px;
            color: var(--text-1);
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input::placeholder { color: var(--text-muted); }

        .form-input:hover { border-color: rgba(0,0,0,0.22); }

        .form-input:focus {
            border-color: var(--accent);
            background: rgba(99,102,241,0.06);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        /* Submit */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 6px;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            letter-spacing: -0.01em;
        }

        .btn-login:hover {
            background: #4f46e5;
            transform: translateY(-1px);
        }

        .btn-login:active { transform: translateY(0) scale(0.99); }

        /* Back link */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 22px 0 18px;
        }

        .back-link {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover { color: var(--text-2); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-brand">
            <div class="brand-mark">
                <i class="fas fa-microchip"></i>
            </div>
            <div class="brand-title">Admin Panel</div>
            <div class="brand-sub">Ban Linh Kiá»‡n â€” Quáº£n trá»‹ há»‡ thá»‘ng</div>
        </div>

        <div class="login-card">
            <?php if ($error): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle" style="flex-shrink:0;"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php echo CsrfHelper::field(); ?>
                <div class="form-group">
                    <label class="form-label" for="username">TÃªn Ä‘Äƒng nháº­p</label>
                    <div class="input-wrap">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-input"
                               placeholder="Nháº­p tÃªn Ä‘Äƒng nháº­p" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Máº­t kháº©u</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-input"
                               placeholder="Nháº­p máº­t kháº©u" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    ÄÄƒng nháº­p
                </button>
            </form>

            <div class="divider"></div>

            <a href="<?php echo BASE_URL ?? '/'; ?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Quay láº¡i trang chá»§
            </a>
        </div>
    </div>
</body>
</html>
