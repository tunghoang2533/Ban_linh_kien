<?php
// AssetHelper autoloaded via PSR-4 + class_alias
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ban Linh Kiện</title>
    <meta name="description" content="Trang quản trị Ban Linh Kiện">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo AssetHelper::url('admin/public/css/admin_style.css'); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="admin-wrapper">
    <script>
    // ── Auto-append CSRF token to ALL POST forms ──
    document.addEventListener('DOMContentLoaded', function() {
        var token = <?php echo json_encode($_SESSION['csrf_token'] ?? ''); ?>;
        if (!token) return;
        document.querySelectorAll('form').forEach(function(form) {
            var method = (form.method || '').toUpperCase();
            if (method !== 'POST') return;
            if (form.querySelector('input[name="_csrf_token"]')) return;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_csrf_token';
            input.value = token;
            form.prepend(input);
        });
    });
    </script>
