<?php
// Admin Settings View
$settings = $settingsCtrl->getAllSettings();
$s = $settings; // shorthand
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-cog" style="color:#6366f1;"></i> Cài đặt Shop</h1>
</div>

<?php if (!empty($successMessage)): ?>
<div class="alert alert-success" style="background:rgba(34,197,94,0.12);border:1px solid #6ee7b7;color:#4ade80;padding:12px 18px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="background:rgba(239,68,68,0.12);border:1px solid #fca5a5;color:#f87171;padding:12px 18px;border-radius:10px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<!-- Tab Navigation -->
<div style="border-bottom:2px solid #e2e8f0;margin-bottom:24px;">
    <div style="display:flex;gap:4px;">
        <?php
        $tabs = [
            'general'  => ['icon'=>'fa-store','label'=>'Thông tin chung'],
            'social'   => ['icon'=>'fa-share-alt','label'=>'Mạng xã hội'],
            'policy'   => ['icon'=>'fa-file-contract','label'=>'Chính sách'],
            'shipping' => ['icon'=>'fa-truck','label'=>'Vận chuyển'],
            'seo'      => ['icon'=>'fa-search','label'=>'SEO'],
        ];
        $activeTab = $_GET['tab'] ?? 'general';
        foreach ($tabs as $key => $tab):
        ?>
        <a href="?page=settings&tab=<?php echo $key; ?>"
           style="padding:10px 20px;font-size:13px;font-weight:600;border-radius:8px 8px 0 0;text-decoration:none;display:flex;align-items:center;gap:6px;transition:all .2s;
                  <?php echo $activeTab === $key ? 'background:#6366f1;color:white;' : 'color:var(--text-muted);background:transparent;'; ?>">
            <i class="fas <?php echo $tab['icon']; ?>"></i> <?php echo $tab['label']; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<form method="POST" action="?page=settings&tab=<?php echo $activeTab; ?>" enctype="multipart/form-data">
    <input type="hidden" name="settings_tab" value="<?php echo $activeTab; ?>">

<?php if ($activeTab === 'general'): ?>
<div class="card" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none;">
    <div class="card-body" style="padding:28px;">
        <h5 style="margin:0 0 24px;font-weight:700;color:var(--text-primary);"><i class="fas fa-store" style="color:#6366f1;"></i> Thông tin cửa hàng</h5>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Tên cửa hàng</label>
                <input type="text" name="shop_name" value="<?php echo htmlspecialchars($s['shop_name'] ?? ''); ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Hotline</label>
                <input type="text" name="shop_hotline" value="<?php echo htmlspecialchars($s['shop_hotline'] ?? ''); ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Email liên hệ</label>
                <input type="email" name="shop_email" value="<?php echo htmlspecialchars($s['shop_email'] ?? ''); ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Địa chỉ</label>
                <input type="text" name="shop_address" value="<?php echo htmlspecialchars($s['shop_address'] ?? ''); ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;">
            </div>
        </div>
        <div style="margin-top:20px;">
            <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:8px;">Logo cửa hàng</label>
            <div style="display:flex;align-items:center;gap:20px;">
                <?php if (!empty($s['shop_logo'])): ?>
                <img src="<?php echo BASE_URL; ?>public/img/<?php echo htmlspecialchars($s['shop_logo']); ?>" 
                     style="width:80px;height:80px;object-fit:contain;border:2px solid #e2e8f0;border-radius:12px;padding:4px;" alt="Logo">
                <?php endif; ?>
                <div>
                    <input type="file" name="shop_logo_file" accept="image/*" style="font-size:13px;">
                    <p style="font-size:11px;color:var(--text-faint);margin:4px 0 0;">PNG, JPG, SVG tối đa 2MB</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($activeTab === 'social'): ?>
<div class="card" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none;">
    <div class="card-body" style="padding:28px;">
        <h5 style="margin:0 0 24px;font-weight:700;color:var(--text-primary);"><i class="fas fa-share-alt" style="color:#6366f1;"></i> Mạng xã hội</h5>
        <?php
        $socialFields = [
            'shop_facebook' => ['label'=>'Facebook URL','icon'=>'fa-facebook','color'=>'#1877f2'],
            'shop_zalo'     => ['label'=>'Zalo số điện thoại','icon'=>'fa-phone','color'=>'#0068ff'],
            'shop_youtube'  => ['label'=>'YouTube Channel URL','icon'=>'fa-youtube','color'=>'#ff0000'],
        ];
        foreach ($socialFields as $key => $field):
        ?>
        <div style="margin-bottom:18px;display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:10px;background:<?php echo $field['color']; ?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fab <?php echo $field['icon']; ?>" style="color:<?php echo $field['color']; ?>;font-size:18px;"></i>
            </div>
            <div style="flex:1;">
                <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px;"><?php echo $field['label']; ?></label>
                <input type="text" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($s[$key] ?? ''); ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;">
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php elseif ($activeTab === 'policy'): ?>
<div class="card" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none;">
    <div class="card-body" style="padding:28px;">
        <h5 style="margin:0 0 24px;font-weight:700;color:var(--text-primary);"><i class="fas fa-file-contract" style="color:#6366f1;"></i> Chính sách</h5>
        <?php
        $policyFields = [
            'policy_return'   => 'Chính sách đổi trả',
            'policy_warranty' => 'Chính sách bảo hành',
            'policy_shipping' => 'Chính sách vận chuyển',
        ];
        foreach ($policyFields as $key => $label):
        ?>
        <div style="margin-bottom:18px;">
            <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;"><?php echo $label; ?></label>
            <textarea name="<?php echo $key; ?>" rows="3" class="form-control"
                      style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;resize:vertical;"><?php echo htmlspecialchars($s[$key] ?? ''); ?></textarea>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php elseif ($activeTab === 'shipping'): ?>
<div class="card" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none;">
    <div class="card-body" style="padding:28px;">
        <h5 style="margin:0 0 24px;font-weight:700;color:var(--text-primary);"><i class="fas fa-truck" style="color:#6366f1;"></i> Cấu hình phí vận chuyển mặc định</h5>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Phí ship mặc định (VNĐ)</label>
                <input type="number" name="default_shipping_fee" value="<?php echo htmlspecialchars($s['default_shipping_fee'] ?? '50000'); ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;" min="0" step="1000">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Đơn tối thiểu miễn phí ship (VNĐ)</label>
                <input type="number" name="free_shipping_min" value="<?php echo htmlspecialchars($s['free_shipping_min'] ?? '500000'); ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;" min="0" step="10000">
                <p style="font-size:11px;color:var(--text-faint);margin:4px 0 0;">Nhập 0 để tắt miễn phí ship</p>
            </div>
        </div>
    </div>
</div>

<?php elseif ($activeTab === 'seo'): ?>
<div class="card" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:none;">
    <div class="card-body" style="padding:28px;">
        <h5 style="margin:0 0 24px;font-weight:700;color:var(--text-primary);"><i class="fas fa-search" style="color:#6366f1;"></i> SEO trang chủ</h5>
        <div style="margin-bottom:18px;">
            <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Meta Title <span style="color:var(--text-faint);font-weight:400;">(50-60 ký tự)</span></label>
            <input type="text" name="meta_title_home" id="seoTitle"
                   value="<?php echo htmlspecialchars($s['meta_title_home'] ?? ''); ?>"
                   class="form-control" maxlength="70" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;" oninput="updatePreview()">
        </div>
        <div style="margin-bottom:24px;">
            <label style="font-size:13px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Meta Description <span style="color:var(--text-faint);font-weight:400;">(150-160 ký tự)</span></label>
            <textarea name="meta_description_home" id="seoDesc" rows="3" class="form-control" maxlength="200"
                      style="border-radius:10px;border:1px solid var(--border-muted);padding:10px 14px;resize:none;" oninput="updatePreview()"><?php echo htmlspecialchars($s['meta_description_home'] ?? ''); ?></textarea>
        </div>
        <div style="background:var(--bg-elevated);border-radius:12px;padding:20px;border:1px solid var(--border-subtle);">
            <p style="font-size:12px;font-weight:700;color:var(--text-muted);margin:0 0 10px;text-transform:uppercase;letter-spacing:.5px;">📊 Google Preview</p>
            <div id="serp-preview">
                <div style="font-size:18px;color:#1a0dab;cursor:pointer;font-family:arial;" id="prev-title"><?php echo htmlspecialchars($s['meta_title_home'] ?? 'Tiêu đề trang'); ?></div>
                <div style="font-size:13px;color:#006621;margin:2px 0;" id="prev-url"><?php echo BASE_URL; ?></div>
                <div style="font-size:13px;color:#545454;line-height:1.5;" id="prev-desc"><?php echo htmlspecialchars($s['meta_description_home'] ?? 'Mô tả trang...'); ?></div>
            </div>
        </div>
        <script>
        function updatePreview() {
            document.getElementById('prev-title').textContent = document.getElementById('seoTitle').value || 'Tiêu đề trang';
            document.getElementById('prev-desc').textContent = document.getElementById('seoDesc').value || 'Mô tả trang...';
        }
        </script>
    </div>
</div>
<?php endif; ?>

<div style="margin-top:24px;display:flex;justify-content:flex-end;">
    <button type="submit" name="save_settings" class="btn btn-primary"
            style="background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;color:white;padding:12px 32px;border-radius:12px;font-weight:700;font-size:14px;cursor:pointer;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-save"></i> Lưu cài đặt
    </button>
</div>
</form>
