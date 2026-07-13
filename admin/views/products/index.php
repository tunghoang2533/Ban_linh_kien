<?php
$products = $admin->getProducts();
$editId = $editId ?? null;
// Lấy bản đồ cảnh báo cho tất cả sản phẩm
$alertMap        = $admin->getProductAlertMap(365, 3);
$countSlow       = $admin->getSlowMovingCount(365);
$countHighReturn = $admin->getHighReturnCount(3);
?>
<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Quản lý sản phẩm</h1>
            <p>Tổng cộng <strong><?php echo count($products); ?></strong> sản phẩm</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <a href="?page=products&action=import" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-file-import"></i> Import CSV
            </a>
            <button type="button" id="showAddProductButton" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm sản phẩm
            </button>
        </div>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($countSlow > 0 || $countHighReturn > 0): ?>
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <?php if ($countSlow > 0): ?>
        <a href="?page=inventory&filter=slow" style="
            text-decoration:none;flex:1;min-width:220px;
            display:flex;align-items:center;gap:12px;
            background:rgba(245,158,11,0.10);
            border:1px solid rgba(245,158,11,0.25);
            border-left:3px solid #f59e0b;border-radius:10px;
            padding:12px 16px;
            transition:all .2s;" onmouseover="this.style.background='rgba(245,158,11,0.16)'" onmouseout="this.style.background='rgba(245,158,11,0.10)'">
            <i class="fas fa-hourglass-half" style="color:#f59e0b;font-size:22px;"></i>
            <div>
                <div style="font-size:18px;font-weight:700;color:#fbbf24;"><?php echo $countSlow; ?> sản phẩm</div>
                <div style="font-size:12px;color:#a1a1aa;">Chưa bán được trong hơn 1 năm &rarr; Xem kho</div>
            </div>
        </a>
        <?php endif; ?>
        <?php if ($countHighReturn > 0): ?>
        <a href="?page=inventory&filter=high_return" style="
            text-decoration:none;flex:1;min-width:220px;
            display:flex;align-items:center;gap:12px;
            background:rgba(236,72,153,0.10);
            border:1px solid rgba(236,72,153,0.25);
            border-left:3px solid #ec4899;border-radius:10px;
            padding:12px 16px;
            transition:all .2s;" onmouseover="this.style.background='rgba(236,72,153,0.18)'" onmouseout="this.style.background='rgba(236,72,153,0.10)'">
            <i class="fas fa-undo-alt" style="color:#ec4899;font-size:22px;"></i>
            <div>
                <div style="font-size:18px;font-weight:700;color:#f9a8d4;"><?php echo $countHighReturn; ?> sản phẩm</div>
                <div style="font-size:12px;color:#a1a1aa;">Bị hoàn hàng nhiều lần (&ge;3 lần) &rarr; Xem kho</div>
            </div>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Add Product Form -->
    <section id="addProductSection" class="form-card <?php echo $showAddForm ? '' : 'hidden'; ?>" style="margin-bottom:24px;max-width:100%;">
        <h2 class="form-section-title"><i class="fas fa-plus-circle" style="color:#6366f1;margin-right:8px;"></i>Thêm sản phẩm mới</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="name">Tên sản phẩm <span class="req">*</span></label>
                    <input class="form-control" type="text" id="name" name="name"
                           value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
                           placeholder="Nhập tên sản phẩm" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="price">Giá (₫) <span class="req">*</span></label>
                    <input class="form-control" type="number" id="price" name="price"
                           value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>"
                           step="1000" min="0" placeholder="VD: 500000" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="quantity">Số lượng</label>
                    <input class="form-control" type="number" id="quantity" name="quantity"
                           value="<?php echo htmlspecialchars($product['quantity'] ?? '0'); ?>"
                           min="0" step="1" placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="category_id">Mã danh mục</label>
                    <input class="form-control" type="text" id="category_id" name="category_id"
                           value="<?php echo htmlspecialchars($product['category_id'] ?? ''); ?>"
                           placeholder="VD: 1">
                </div>
                <div class="form-group">
                    <label class="form-label" for="brand_id">Mã thương hiệu</label>
                    <input class="form-control" type="text" id="brand_id" name="brand_id"
                           value="<?php echo htmlspecialchars($product['brand_id'] ?? ''); ?>"
                           placeholder="VD: 1">
                </div>
                <div class="form-group">
                    <label class="form-label" for="image">Ảnh chính (bìa) <span class="req">*</span></label>
                    <input class="form-control" type="file" id="image" name="image" accept="image/*" onchange="previewAddMain(this)">
                    <p class="form-note"><i class="fas fa-info-circle"></i> Định dạng: JPG, PNG, GIF, WEBP</p>
                    <div id="add-main-preview" style="margin-top:6px;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_extra_images">Ảnh phụ (chọn nhiều)</label>
                    <input class="form-control" type="file" id="add_extra_images" name="extra_images[]" accept="image/*" multiple onchange="previewAddExtra(this)">
                    <p class="form-note"><i class="fas fa-info-circle"></i> Giữ Ctrl+Click để chọn nhiều ảnh</p>
                    <div id="add-extra-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="discount_percent">Giảm giá (%)</label>
                    <input class="form-control" type="number" id="discount_percent" name="discount_percent"
                           value="0" step="0.5" min="0" max="100" placeholder="0">
                    <p class="form-note"><i class="fas fa-info-circle"></i> 0 = không giảm giá. VD: 10 = giảm 10%</p>
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label" for="description">Mô tả</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                              placeholder="Mô tả sản phẩm..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu sản phẩm
                </button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addProductSection').classList.add('hidden')">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>
    </section>

    <!-- Products Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hình ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Thương hiệu</th>
                    <th>Danh mục</th>
                    <th>Số lượng</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $productItem): ?>
                    <tr style="<?php echo !$productItem['is_active'] ? 'opacity:0.45;' : ''; ?>">
                        <td><span style="font-weight:700;color:#6366f1;">#<?php echo $productItem['id']; ?></span></td>
                        <td>
                            <img src="<?php echo strpos($productItem['image'], 'data:') === 0 ? $productItem['image'] : BASE_URL . 'public/img/products/' . $productItem['image']; ?>"
                                 alt="<?php echo htmlspecialchars($productItem['name']); ?>"
                                 class="table-img"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'44\' height=\'44\' viewBox=\'0 0 44 44\'%3E%3Crect width=\'44\' height=\'44\' fill=\'%23f1f5f9\' rx=\'10\'/%3E%3Ctext x=\'22\' y=\'27\' font-size=\'18\' text-anchor=\'middle\' fill=\'%2394a3b8\'%3E📦%3C/text%3E%3C/svg%3E'">
                        </td>
                        <td>
                            <span style="font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($productItem['name']); ?></span>
                        </td>
                        <td style="font-weight:700;color:#6366f1;"><?php echo number_format($productItem['price'], 0, ',', '.'); ?> ₫</td>
                        <td>
                            <span style="background:var(--bg-elevated);padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;color:var(--text-secondary);">
                                <?php echo htmlspecialchars($productItem['brand_id']); ?>
                            </span>
                        </td>
                        <td>
                            <span style="background:#eff6ff;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;color:#3b82f6;">
                                <?php echo htmlspecialchars($productItem['category_name'] ?? $productItem['category_id']); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $pAlert       = $alertMap[$productItem['id']] ?? [];
                            $isSlow       = !empty($pAlert['slow']);
                            $isHighReturn = !empty($pAlert['high_return']);
                            ?>
                            <span style="font-weight:600;<?php echo intval($productItem['quantity']) < 5 ? 'color:#ef4444;' : 'color:#10b981;'; ?>">
                                <?php echo intval($productItem['quantity']); ?>
                            </span>
                            <?php if ($isSlow): ?>
                            <div style="margin-top:3px;">
                                <span title="Chưa bán được trong hơn 1 năm<?php echo !empty($pAlert['last_sold_at']) ? ' (cuối: '.date('d/m/Y', strtotime($pAlert['last_sold_at'])).')' : ' (chưa từng bán)'; ?>" style="display:inline-flex;align-items:center;gap:3px;background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid #fbbf24;border-radius:20px;padding:1px 7px;font-size:10px;font-weight:700;cursor:help;">
                                    <i class="fas fa-hourglass-half" style="font-size:9px;"></i> Ế hàng
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if ($isHighReturn): ?>
                            <div style="margin-top:3px;">
                                <span title="Hoàn hàng <?php echo $pAlert['return_count']; ?> lần" style="display:inline-flex;align-items:center;gap:3px;background:#fce7f3;color:#9d174d;border:1px solid #f9a8d4;border-radius:20px;padding:1px 7px;font-size:10px;font-weight:700;cursor:help;">
                                    <i class="fas fa-undo-alt" style="font-size:9px;"></i> Hoàn <?php echo $pAlert['return_count']; ?>x
                                </span>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--text-muted);"><?php echo date('d/m/Y', strtotime($productItem['created_at'])); ?></td>
                        <td>
                            <form method="post" action="?page=products&action=toggle_status" style="display:inline;">
                            <?php echo CsrfHelper::field(); ?>
                            <input type="hidden" name="id" value="<?php echo $productItem['id']; ?>">
                            <button type="submit"
                               title="<?php echo $productItem['is_active'] ? 'Nhấn để ẩn sản phẩm' : 'Nhấn để hiện sản phẩm'; ?>"
                               style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:99px;font-size:11px;font-weight:700;text-decoration:none;transition:all .2s;
                               <?php echo $productItem['is_active']
                                   ? 'background:rgba(34,197,94,0.12);color:#4ade80;'
                                   : 'background:rgba(239,68,68,0.12);color:#f87171;'; ?>">
                                <i class="fas <?php echo $productItem['is_active'] ? 'fa-eye' : 'fa-eye-slash'; ?>" style="font-size:13px;"></i>
                                <?php echo $productItem['is_active'] ? 'Hiển thị' : 'Đã ẩn'; ?>
                            </button>
                            </form>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="?page=products&edit_id=<?php echo $productItem['id']; ?>"
                                   class="btn btn-sm btn-warning toggle-edit-button"
                                   data-edit-id="<?php echo $productItem['id']; ?>">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="?page=products&action=price_history&id=<?php echo $productItem['id']; ?>"
                                   class="btn btn-sm"
                                   style="background:rgba(99,102,241,0.12);color:#818cf8;border:1px solid rgba(99,102,241,0.25);"
                                   title="Lịch sử giá">
                                    <i class="fas fa-chart-line"></i> Giá
                                </a>
                                <a href="?page=products&action=delete&id=<?php echo $productItem['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php if ($editId === intval($productItem['id'])): ?>
                        <tr id="edit-<?php echo $productItem['id']; ?>" class="edit-row">
                            <td colspan="10">
                                <div class="edit-form-card">
                                    <h3 class="form-section-title">
                                        <i class="fas fa-edit" style="color:#f59e0b;margin-right:8px;"></i>
                                        Chỉnh sửa sản phẩm #<?php echo $productItem['id']; ?>
                                    </h3>
                                    <form method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="product_id" value="<?php echo $productItem['id']; ?>">
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label" for="edit-name-<?php echo $productItem['id']; ?>">Tên sản phẩm <span class="req">*</span></label>
                                                <input class="form-control" type="text"
                                                       id="edit-name-<?php echo $productItem['id']; ?>"
                                                       name="name"
                                                       value="<?php echo htmlspecialchars($product['name'] ?? $productItem['name']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="edit-price-<?php echo $productItem['id']; ?>">Giá (₫) <span class="req">*</span></label>
                                                <input class="form-control" type="number"
                                                       id="edit-price-<?php echo $productItem['id']; ?>"
                                                       name="price"
                                                       value="<?php echo htmlspecialchars($product['price'] ?? $productItem['price']); ?>"
                                                       step="1000" min="0" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="edit-quantity-<?php echo $productItem['id']; ?>">Số lượng</label>
                                                <input class="form-control" type="number"
                                                       id="edit-quantity-<?php echo $productItem['id']; ?>"
                                                       name="quantity"
                                                       value="<?php echo htmlspecialchars($product['quantity'] ?? $productItem['quantity']); ?>"
                                                       min="0" step="1">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="edit-category_id-<?php echo $productItem['id']; ?>">Mã danh mục</label>
                                                <input class="form-control" type="text"
                                                       id="edit-category_id-<?php echo $productItem['id']; ?>"
                                                       name="category_id"
                                                       value="<?php echo htmlspecialchars($product['category_id'] ?? $productItem['category_id']); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="edit-brand_id-<?php echo $productItem['id']; ?>">Mã thương hiệu</label>
                                                <input class="form-control" type="text"
                                                       id="edit-brand_id-<?php echo $productItem['id']; ?>"
                                                       name="brand_id"
                                                       value="<?php echo htmlspecialchars($product['brand_id'] ?? $productItem['brand_id']); ?>">
                                            </div>
                                             <div class="form-group">
                                                 <label class="form-label" for="edit-image-<?php echo $productItem['id']; ?>">Ảnh chính (bìa)</label>
                                                 <?php if (!empty($productItem['image'])): ?>
                                                 <div style="margin-bottom:8px;">
                                                     <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($productItem['image']); ?>"
                                                          style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:2px solid #ff9800;">
                                                 </div>
                                                 <?php endif; ?>
                                                 <input class="form-control" type="file"
                                                        id="edit-image-<?php echo $productItem['id']; ?>"
                                                        name="image" accept="image/*"
                                                        onchange="previewEditMain(this, <?php echo $productItem['id']; ?>)">
                                                 <div id="edit-main-preview-<?php echo $productItem['id']; ?>" style="margin-top:6px;"></div>
                                                 <p class="form-note"><i class="fas fa-info-circle"></i> Để trống nếu không đổi ảnh.</p>
                                             </div>
                                             <?php
                                             $editExtraImages = $admin->getProductImages($productItem['id']);
                                             ?>
                                             <?php if (!empty($editExtraImages)): ?>
                                             <div class="form-group form-group-full">
                                                 <label class="form-label">Ảnh phụ hiện có</label>
                                                 <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;">
                                                     <?php foreach ($editExtraImages as $eImg): ?>
                                                     <div style="position:relative;display:inline-block;">
                                                         <img src="<?php echo BASE_URL . 'public/img/products/' . htmlspecialchars($eImg['image']); ?>"
                                                              style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1.5px solid #e5e7eb;">
                                                         <a href="?page=products&action=delete_image&image_id=<?php echo $eImg['id']; ?>&pid=<?php echo $productItem['id']; ?>"
                                                            onclick="return confirm('Xóa ảnh này?')"
                                                            style="position:absolute;top:-7px;right:-7px;background:#e10c00;color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;text-decoration:none;line-height:1;"
                                                            title="Xóa ảnh">×</a>
                                                     </div>
                                                     <?php endforeach; ?>
                                                 </div>
                                             </div>
                                             <?php endif; ?>
                                             <div class="form-group form-group-full">
                                                 <label class="form-label" for="edit-extra-<?php echo $productItem['id']; ?>">Thêm ảnh phụ mới</label>
                                                 <input class="form-control" type="file"
                                                        id="edit-extra-<?php echo $productItem['id']; ?>"
                                                        name="extra_images[]" accept="image/*" multiple
                                                        onchange="previewEditExtra(this, <?php echo $productItem['id']; ?>)">
                                                 <p class="form-note"><i class="fas fa-info-circle"></i> Giữ Ctrl+Click để chọn nhiều ảnh</p>
                                                 <div id="edit-extra-preview-<?php echo $productItem['id']; ?>" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;"></div>
                                             </div>
                                            <div class="form-group form-group-full">
                                                <label class="form-label">
                                                    Giảm giá (%)
                                                    <a href="?page=products&action=price_history&id=<?php echo $productItem['id']; ?>"
                                                       style="font-size:11px;font-weight:500;color:#818cf8;margin-left:10px;text-decoration:none;"
                                                       target="_blank">
                                                        <i class="fas fa-chart-line"></i> Lịch sử giá
                                                    </a>
                                                </label>
                                                <input class="form-control" type="number"
                                                       id="edit-discount-<?php echo $productItem['id']; ?>"
                                                       name="discount_percent"
                                                       value="<?php echo htmlspecialchars($product['discount_percent'] ?? $productItem['discount_percent'] ?? 0); ?>"
                                                       step="0.5" min="0" max="100" placeholder="0 = không giảm giá">
                                            </div>
                                            <div class="form-group form-group-full">
                                                <label class="form-label" for="edit-description-<?php echo $productItem['id']; ?>">Mô tả</label>
                                                <textarea class="form-control"
                                                          id="edit-description-<?php echo $productItem['id']; ?>"
                                                          name="description" rows="3"><?php echo htmlspecialchars($product['description'] ?? $productItem['description']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Cập nhật
                                            </button>
                                            <a href="?page=products" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Hủy
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    window.adminEditId = <?php echo json_encode($editId); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        // Edit form scroll & highlight
        let editForm = null;
        const hash = window.location.hash;
        if (hash && hash.startsWith('#edit-')) {
            editForm = document.querySelector(hash);
        } else if (window.adminEditId) {
            editForm = document.querySelector('.edit-row');
        }

        if (editForm) {
            editForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            editForm.style.transition = 'background 0.5s ease';
            editForm.style.background = '#fef9c3';
            setTimeout(() => { editForm.style.background = ''; }, 2500);

            const firstInput = editForm.querySelector('input[type="text"], input[type="number"]');
            if (firstInput) firstInput.focus();
        }
    });

    // Preview ảnh chính khi thêm mới
    function previewAddMain(input) {
        var preview = document.getElementById('add-main-preview');
        preview.innerHTML = '';
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-height:100px;border-radius:6px;border:2px solid #e10c00;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Preview ảnh phụ khi thêm mới
    function previewAddExtra(input) {
        var preview = document.getElementById('add-extra-preview');
        preview.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(function(file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #ddd;';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    // Preview ảnh chính khi sửa
    function previewEditMain(input, pid) {
        var preview = document.getElementById('edit-main-preview-' + pid);
        if (!preview) return;
        preview.innerHTML = '';
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-height:100px;border-radius:6px;border:2px solid #e10c00;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Preview ảnh phụ khi sửa
    function previewEditExtra(input, pid) {
        var preview = document.getElementById('edit-extra-preview-' + pid);
        if (!preview) return;
        preview.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(function(file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #ddd;';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    }
</script>
