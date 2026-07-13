<?php
// Admin SEO View
if (!isset($settingsCtrl)) {
    require_once __DIR__ . '/../../controllers/SettingsController.php';
    $settingsCtrl = new SettingsController($db);
}
$s = $settingsCtrl->getAllSettings();
$products = $db->query("SELECT id, name, meta_title, meta_description, meta_keywords FROM products WHERE is_active=1 ORDER BY name ASC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header" style="margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-search" style="color:#6366f1;"></i> Quản lý SEO</h1>
</div>

<?php if (!empty($successMessage)): ?>
<div style="background:rgba(34,197,94,0.12);border:1px solid #6ee7b7;color:#4ade80;padding:12px 18px;border-radius:10px;margin-bottom:20px;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<!-- Tab Navigation -->
<div style="border-bottom:2px solid #e2e8f0;margin-bottom:24px;display:flex;gap:4px;">
    <?php $seoTab = $_GET['stab'] ?? 'home'; ?>
    <?php foreach (['home'=>['fa-home','Trang chủ'],'products'=>['fa-box','Sản phẩm'],'guide'=>['fa-book','Hướng dẫn SEO']] as $tab => [$icon,$label]): ?>
    <a href="?page=seo&stab=<?php echo $tab; ?>" style="padding:10px 20px;border-radius:8px 8px 0 0;text-decoration:none;display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;<?php echo $seoTab===$tab?'background:#6366f1;color:white;':'color:var(--text-muted);'; ?>">
        <i class="fas <?php echo $icon; ?>"></i><?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($seoTab === 'home'): ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:24px;">
        <h5 style="margin:0 0 20px;font-weight:700;"><i class="fas fa-home" style="color:#6366f1;"></i> Meta trang chủ</h5>
        <form method="POST">
            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Meta Title <span style="color:var(--text-faint);font-weight:400;">(≤60 ký tự)</span></label>
                <input type="text" name="meta_title_home" id="seoTitle" value="<?php echo htmlspecialchars($s['meta_title_home'] ?? ''); ?>" maxlength="70" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" oninput="updatePreview()">
                <p style="font-size:11px;color:var(--text-faint);margin:4px 0 0;"><span id="titleCount">0</span>/60 ký tự</p>
            </div>
            <div style="margin-bottom:24px;">
                <label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Meta Description <span style="color:var(--text-faint);font-weight:400;">(≤160 ký tự)</span></label>
                <textarea name="meta_description_home" id="seoDesc" rows="3" maxlength="200" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:none;" oninput="updatePreview()"><?php echo htmlspecialchars($s['meta_description_home'] ?? ''); ?></textarea>
                <p style="font-size:11px;color:var(--text-faint);margin:4px 0 0;"><span id="descCount">0</span>/160 ký tự</p>
            </div>
            <button type="submit" name="save_seo_home" class="btn btn-primary" style="border-radius:12px;padding:11px 28px;font-weight:700;background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;"><i class="fas fa-save"></i> Lưu meta trang chủ</button>
        </form>
    </div></div>

    <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:24px;">
        <h5 style="margin:0 0 20px;font-weight:700;"><i class="fab fa-google" style="color:#4285f4;"></i> Xem trước Google SERP</h5>
        <div style="background:var(--bg-surface);border:1px solid var(--border-subtle);border-radius:12px;padding:20px;">
            <div id="prev-url" style="font-size:13px;color:#202124;margin-bottom:2px;display:flex;align-items:center;gap:6px;">
                <span style="width:20px;height:20px;background:#f1f3f4;border-radius:50%;display:inline-block;"></span>
                <span style="color:#202124;"><?php echo BASE_URL; ?></span>
            </div>
            <div id="prev-title" style="font-size:20px;color:#1a0dab;cursor:pointer;line-height:1.3;margin:4px 0;"><?php echo htmlspecialchars($s['meta_title_home'] ?? 'Tiêu đề trang'); ?></div>
            <div id="prev-desc" style="font-size:14px;color:#4d5156;line-height:1.58;"><?php echo htmlspecialchars($s['meta_description_home'] ?? 'Mô tả trang...'); ?></div>
        </div>

        <div style="margin-top:20px;">
            <h6 style="font-size:13px;font-weight:700;color:var(--text-secondary);margin:0 0 12px;">Checklist SEO trang chủ</h6>
            <?php
            $checks = [
                ['label'=>'Meta Title có từ 10-60 ký tự', 'ok' => strlen($s['meta_title_home']??'') >= 10 && strlen($s['meta_title_home']??'') <= 60],
                ['label'=>'Meta Description có từ 50-160 ký tự', 'ok' => strlen($s['meta_description_home']??'') >= 50 && strlen($s['meta_description_home']??'') <= 160],
                ['label'=>'Có số điện thoại/hotline', 'ok' => !empty($s['shop_hotline'])],
                ['label'=>'Có địa chỉ cửa hàng', 'ok' => !empty($s['shop_address'])],
            ];
            foreach ($checks as $c):
            ?>
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border-subtle);">
                <span style="font-size:16px;"><?php echo $c['ok'] ? '✅' : '❌'; ?></span>
                <span style="font-size:13px;color:<?php echo $c['ok']?'#166534':'#991b1b'; ?>;"><?php echo $c['label']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div></div>
</div>

<script>
function updatePreview() {
    const title = document.getElementById('seoTitle').value;
    const desc = document.getElementById('seoDesc').value;
    document.getElementById('prev-title').textContent = title || 'Tiêu đề trang';
    document.getElementById('prev-desc').textContent = desc || 'Mô tả trang...';
    document.getElementById('titleCount').textContent = title.length;
    document.getElementById('descCount').textContent = desc.length;
}
document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<?php elseif ($seoTab === 'products'): ?>
<form method="POST">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <p style="margin:0;color:var(--text-muted);font-size:13px;">Cấu hình meta title & description cho từng sản phẩm (ảnh hưởng SEO tìm kiếm)</p>
        <button type="submit" name="save_product_meta" class="btn btn-primary" style="border-radius:10px;padding:9px 20px;font-weight:700;background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;"><i class="fas fa-save"></i> Lưu tất cả</button>
    </div>
    <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:0;">
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:var(--bg-elevated);">
            <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);text-align:left;width:25%;">SẢN PHẨM</th>
            <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);width:28%;">META TITLE</th>
            <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);width:30%;">META DESCRIPTION</th>
            <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);width:17%;">KEYWORDS</th>
        </tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
        <input type="hidden" name="product_ids[]" value="<?php echo $p['id']; ?>">
        <tr style="border-bottom:1px solid var(--border-subtle);">
            <td style="padding:12px 16px;font-size:13px;font-weight:700;color:var(--text-primary);"><?php echo htmlspecialchars(mb_substr($p['name'],0,40)); ?></td>
            <td style="padding:8px 16px;"><input type="text" name="meta_title[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($p['meta_title']??''); ?>" placeholder="Auto từ tên SP..." class="form-control" style="border-radius:8px;border:1px solid var(--border-muted);padding:7px 10px;font-size:12px;" maxlength="70"></td>
            <td style="padding:8px 16px;"><input type="text" name="meta_description[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($p['meta_description']??''); ?>" placeholder="Mô tả ngắn..." class="form-control" style="border-radius:8px;border:1px solid var(--border-muted);padding:7px 10px;font-size:12px;" maxlength="160"></td>
            <td style="padding:8px 16px;"><input type="text" name="meta_keywords[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($p['meta_keywords']??''); ?>" placeholder="từ,khóa..." class="form-control" style="border-radius:8px;border:1px solid var(--border-muted);padding:7px 10px;font-size:12px;"></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div></div>
</form>

<?php else: // guide ?>
<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
<div class="card-body" style="padding:32px;">
    <h5 style="margin:0 0 24px;font-weight:800;color:var(--text-primary);font-size:18px;"><i class="fas fa-lightbulb" style="color:#f59e0b;"></i> Hướng dẫn tối ưu SEO</h5>
    <?php
    $guides = [
        ['📌 Meta Title','<strong>Độ dài lý tưởng:</strong> 50–60 ký tự.<br>Chứa từ khóa chính, tên thương hiệu. Tránh lặp từ và viết hoa toàn bộ.','#eff6ff','#2563eb'],
        ['📝 Meta Description','<strong>Độ dài lý tưởng:</strong> 130–160 ký tự.<br>Mô tả hấp dẫn, có call-to-action. Chứa từ khóa phụ.','#fefce8','#ca8a04'],
        ['🔗 URL thân thiện','Sử dụng slug ngắn gọn, có từ khóa, viết thường, dùng dấu gạch ngang.','#f0fdf4','#16a34a'],
        ['🖼 Ảnh sản phẩm','Đặt tên file ảnh có nghĩa. Luôn thêm thuộc tính <code>alt</code> mô tả ảnh.','#fdf4ff','#7c3aed'],
        ['⚡ Tốc độ tải trang','Nén ảnh WebP, sử dụng lazy loading, cache trình duyệt. Mục tiêu <3 giây.','#fff7ed','#ea580c'],
        ['📱 Mobile-first','Google ưu tiên index mobile. Đảm bảo giao diện responsive hoàn toàn.','#f0fdf4','#059669'],
    ];
    foreach ($guides as [$title, $content, $bg, $color]):
    ?>
    <div style="background:<?php echo $bg; ?>;border-radius:14px;padding:18px 20px;margin-bottom:14px;border-left:4px solid <?php echo $color; ?>;">
        <h6 style="margin:0 0 8px;font-size:14px;font-weight:800;color:var(--text-primary);"><?php echo $title; ?></h6>
        <p style="margin:0;font-size:13px;color:var(--text-secondary);line-height:1.6;"><?php echo $content; ?></p>
    </div>
    <?php endforeach; ?>
</div></div>
<?php endif; ?>
