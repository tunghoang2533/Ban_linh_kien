<?php
/**
 * Import sản phẩm từ file CSV/Excel
 * Cho phép tải lên file, xem trước, map cột, và nhập hàng loạt
 */

// Process import
$importResult = null;
$importPreview = [];
$importColumns = [];
$importErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_confirm'])) {
    // Bước 2: Xác nhận import
    $mappedColumns = $_POST['col_map'] ?? [];
    $dataRows = json_decode($_POST['import_data'] ?? '[]', true);
    
    if (empty($dataRows)) {
        $importErrors[] = 'Không có dữ liệu để import.';
    } else {
        $successCount = 0;
        $errorCount = 0;
        $errorDetails = [];
        
        foreach ($dataRows as $idx => $row) {
            try {
                $prodData = [
                    'category_id'    => intval($row['category_id'] ?? 0),
                    'brand_id'       => intval($row['brand_id'] ?? 0),
                    'name'           => trim($row['name'] ?? ''),
                    'price'          => floatval(str_replace(['.', ','], ['', '.'], $row['price'] ?? 0)),
                    'cost_price'     => floatval(str_replace(['.', ','], ['', '.'], $row['cost_price'] ?? 0)),
                    'quantity'       => intval($row['quantity'] ?? 0),
                    'discount_percent' => floatval($row['discount_percent'] ?? 0),
                    'description'    => trim($row['description'] ?? ''),
                    'image'          => '',
                ];
                
                if (empty($prodData['name']) || $prodData['price'] <= 0) {
                    throw new Exception("Thiếu tên hoặc giá không hợp lệ (row #" . ($idx+2) . ")");
                }
                
                // Kiểm tra sản phẩm đã tồn tại (theo tên)
                $checkStmt = $db->prepare("SELECT id FROM products WHERE name = :name LIMIT 1");
                $checkStmt->execute([':name' => $prodData['name']]);
                $existing = $checkStmt->fetchColumn();
                
                if ($existing && isset($_POST['update_existing'])) {
                    // Cập nhật sản phẩm hiện có
                    $admin->updateProduct($existing, $prodData);
                    $successCount++;
                } elseif (!$existing) {
                    // Thêm mới
                    $admin->addProduct($prodData);
                    $successCount++;
                } else {
                    // Bỏ qua
                    $errorDetails[] = "Row #" . ($idx+2) . ": '{$prodData['name']}' đã tồn tại (bỏ qua)";
                    $errorCount++;
                }
            } catch (Exception $e) {
                $errorDetails[] = $e->getMessage();
                $errorCount++;
            }
        }
        
        // Ghi log
        $db->prepare("INSERT INTO import_export_logs (type, entity_type, total_rows, success_rows, error_rows, errors, created_by) VALUES (?,?,?,?,?,?,?)")
           ->execute(['import', 'products', count($dataRows), $successCount, $errorCount, json_encode($errorDetails), $_SESSION['user_id'] ?? null]);
        
        $importResult = ['success' => $successCount, 'error' => $errorCount, 'details' => $errorDetails];
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    // Bước 1: Upload file + preview
    $file = $_FILES['import_file'];
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        $importErrors[] = 'Lỗi upload file. Vui lòng thử lại.';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $importErrors[] = 'Chỉ hỗ trợ file CSV hoặc Excel (.xls, .xlsx).';
        } else {
            // Parse file
            $rows = [];
            if ($ext === 'csv') {
                $handle = fopen($file['tmp_name'], 'r');
                if ($handle) {
                    $headers = fgetcsv($handle);
                    if (!$headers) {
                        $importErrors[] = 'File CSV không có dữ liệu hoặc không đúng định dạng.';
                    } else {
                        // Clean BOM
                        $headers[0] = preg_replace('/^\\xEF\\xBB\\xBF/', '', $headers[0]);
                        $importColumns = $headers;
                        while (($data = fgetcsv($handle)) !== false) {
                            $row = [];
                            foreach ($headers as $i => $h) {
                                $row[trim($h)] = trim($data[$i] ?? '');
                            }
                            $rows[] = $row;
                        }
                    }
                    fclose($handle);
                }
            } else {
                // Excel: đọc bằng SimpleXLSX hoặc đọc dạng HTML
                // Vì không có thư viện, đọc dạng CSV-like
                $importErrors[] = 'Định dạng Excel cần thư viện PhpSpreadsheet. Vui lòng chuyển sang file CSV.';
            }
            
            if (!empty($rows)) {
                $importPreview = $rows;
            }
        }
    }
}

// Lấy danh mục và thương hiệu cho mapping
$catStmt = $db->query("SELECT id, name FROM categories ORDER BY name");
$allCats = $catStmt->fetchAll(PDO::FETCH_ASSOC);
$brandStmt = $db->query("SELECT id, name FROM brands ORDER BY name");
$allBrands = $brandStmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy lịch sử import
$importLogs = $db->query("SELECT * FROM import_export_logs WHERE type='import' AND entity_type='products' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-file-import" style="color:#22c55e;margin-right:10px;"></i>Import sản phẩm từ CSV</h1>
            <p>Nhập hàng loạt sản phẩm từ file Excel/CSV — tiết kiệm thời gian nhập liệu</p>
        </div>
        <a href="?page=products" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại sản phẩm</a>
    </div>

    <?php if ($importResult): ?>
    <!-- Kết quả import -->
    <div style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-left:5px solid #10b981;border-radius:12px;padding:24px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="width:60px;height:60px;border-radius:50%;background:rgba(16,185,129,.2);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-check-circle" style="color:#10b981;font-size:28px;"></i>
            </div>
            <div>
                <h3 style="margin:0 0 4px;color:#065f46;">Import hoàn tất! ✅</h3>
                <p style="margin:0;color:#047857;font-size:14px;">
                    Thành công: <strong><?php echo $importResult['success']; ?></strong> sản phẩm |
                    Lỗi/Bỏ qua: <strong><?php echo $importResult['error']; ?></strong>
                </p>
            </div>
        </div>
        <?php if (!empty($importResult['details'])): ?>
        <div style="margin-top:12px;padding:12px;background:rgba(255,255,255,.5);border-radius:8px;max-height:150px;overflow-y:auto;">
            <p style="font-size:12px;font-weight:600;color:#92400e;margin:0 0 6px;">Chi tiết lỗi:</p>
            <?php foreach ($importResult['details'] as $d): ?>
            <p style="font-size:11px;color:#b45309;margin:2px 0;">• <?php echo htmlspecialchars($d); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div style="margin-top:16px;">
            <a href="?page=products" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> Xem danh sách sản phẩm</a>
            <a href="?page=products&action=import" class="btn btn-secondary btn-sm"><i class="fas fa-redo"></i> Import tiếp</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($importErrors)): ?>
    <div style="background:linear-gradient(135deg,#fee2e2,#fecaca);border-left:5px solid #ef4444;border-radius:12px;padding:16px 20px;margin-bottom:20px;">
        <p style="font-weight:700;color:#991b1b;margin:0 0 6px;"><i class="fas fa-exclamation-circle"></i> Lỗi:</p>
        <?php foreach ($importErrors as $e): ?>
        <p style="margin:2px 0;font-size:13px;color:#b91c1c;">• <?php echo htmlspecialchars($e); ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($importPreview)): ?>
    <!-- Hướng dẫn + Upload form -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
        <section class="form-card" style="max-width:100%;">
            <h2 class="form-section-title"><i class="fas fa-upload" style="color:#22c55e;margin-right:8px;"></i> Upload file sản phẩm</h2>
            <form method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:14px;">
                <div class="form-group">
                    <label class="form-label">Chọn file CSV <span class="req">*</span></label>
                    <input type="file" name="import_file" class="form-control" accept=".csv" required>
                    <p class="form-note">Định dạng CSV (UTF-8). Tải file mẫu bên dưới để xem cấu trúc.</p>
                </div>
                <div class="form-group">
                    <label class="form-check-label">
                        <input type="checkbox" name="update_existing" value="1" checked>
                        Cập nhật sản phẩm đã tồn tại (theo tên)
                    </label>
                </div>
                <button type="submit" class="btn btn-success" style="width:100%;">
                    <i class="fas fa-upload"></i> Tải lên & Xem trước
                </button>
            </form>
        </section>

        <section class="form-card" style="max-width:100%;">
            <h2 class="form-section-title"><i class="fas fa-download" style="color:#6366f1;margin-right:8px;"></i> Export / Tải mẫu</h2>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <a href="?page=products&action=export&type=csv" class="btn btn-primary" style="justify-content:center;">
                    <i class="fas fa-file-csv"></i> Export sản phẩm ra CSV
                </a>
                <a href="?page=products&action=export&type=template" class="btn btn-secondary" style="justify-content:center;">
                    <i class="fas fa-file-download"></i> Tải file mẫu (Template)
                </a>
                <div style="margin-top:8px;background:var(--bg-elevated);border-radius:8px;padding:14px;">
                    <h4 style="margin:0 0 8px;font-size:13px;font-weight:700;">Cấu trúc file CSV mẫu:</h4>
                    <pre style="font-size:10px;color:var(--text-muted);white-space:pre-wrap;margin:0;">
name,price,cost_price,category_id,brand_id,quantity,discount_percent,description
"RAM DDR4 8GB",500000,350000,1,1,10,0,"RAM chính hãng"
"Ổ SSD 240GB",700000,500000,1,1,5,10,"SSD tốc độ cao"</pre>
                    <p class="form-note" style="margin-top:8px;">Cột bắt buộc: name, price. Các cột còn lại tùy chọn.</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Lịch sử import -->
    <?php if (!empty($importLogs)): ?>
    <section class="form-card" style="max-width:100%;margin-top:20px;">
        <h2 class="form-section-title"><i class="fas fa-history" style="margin-right:8px;"></i> Lịch sử import gần đây</h2>
        <div class="table-responsive" style="border-radius:0;box-shadow:none;border:none;margin:0;">
            <table class="admin-table" style="margin:0;">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>File</th>
                        <th>Tổng</th>
                        <th>Thành công</th>
                        <th>Lỗi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($importLogs as $log): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                        <td><span style="font-size:12px;"><?php echo htmlspecialchars($log['file_name'] ?? '—'); ?></span></td>
                        <td><?php echo $log['total_rows']; ?></td>
                        <td style="color:#10b981;font-weight:700;"><?php echo $log['success_rows']; ?></td>
                        <td style="color:#ef4444;"><?php echo $log['error_rows']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

    <?php else: ?>
    <!-- Bước 2: Preview + Map cột -->
    <section class="form-card" style="max-width:100%;">
        <h2 class="form-section-title">
            <i class="fas fa-eye" style="color:#6366f1;margin-right:8px;"></i>
            Xem trước dữ liệu — <strong><?php echo count($importPreview); ?></strong> dòng
        </h2>
        <p class="form-note">Kiểm tra dữ liệu trước khi import. Các cột được map tự động, bạn có thể điều chỉnh nếu cần.</p>

        <form method="POST">
            <input type="hidden" name="import_confirm" value="1">
            <input type="hidden" name="import_data" value='<?php echo htmlspecialchars(json_encode($importPreview)); ?>'>

            <div style="overflow-x:auto;margin-bottom:16px;">
                <table class="admin-table" style="margin:0;font-size:12px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <?php 
                            $sampleRow = $importPreview[0] ?? [];
                            $knownColumns = ['name','price','cost_price','category_id','brand_id','quantity','discount_percent','description','image'];
                            foreach (array_keys($sampleRow) as $col): 
                                $autoMap = in_array(strtolower($col), $knownColumns) ? strtolower($col) : '';
                            ?>
                            <th>
                                <select name="col_map[]" class="form-control" style="font-size:10px;padding:4px 6px;max-width:130px;">
                                    <option value="">— Bỏ qua —</option>
                                    <?php foreach ($knownColumns as $kc): ?>
                                    <option value="<?php echo $kc; ?>" <?php echo $autoMap === $kc ? 'selected' : ''; ?>><?php echo $kc; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div style="font-size:9px;color:var(--text-faint);margin-top:2px;white-space:nowrap;">📄 <?php echo htmlspecialchars($col); ?></div>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($importPreview, 0, 10) as $idx => $row): ?>
                        <tr>
                            <td style="color:var(--text-faint);font-weight:600;"><?php echo $idx + 2; ?></td>
                            <?php foreach ($row as $val): ?>
                            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars(mb_strimwidth($val, 0, 40, '...')); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (count($importPreview) > 10): ?>
            <p style="font-size:12px;color:var(--text-muted);text-align:center;margin-bottom:16px;">
                <i class="fas fa-ellipsis-h"></i> Hiển thị 10 / <?php echo count($importPreview); ?> dòng
            </p>
            <?php endif; ?>

            <div style="background:var(--bg-elevated);border-radius:10px;padding:14px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
                <i class="fas fa-info-circle" style="color:#6366f1;"></i>
                <span style="font-size:13px;">
                    Tổng số: <strong><?php echo count($importPreview); ?></strong> sản phẩm sẽ được import.
                    <?php if (isset($_POST['update_existing']) || !empty($_POST['update_existing'])): ?>
                    Sản phẩm trùng tên sẽ được <strong>cập nhật</strong>.
                    <?php endif; ?>
                </span>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-success" style="flex:2;" onclick="return confirm('Xác nhận import <?php echo count($importPreview); ?> sản phẩm?')">
                    <i class="fas fa-check"></i> Xác nhận Import (<?php echo count($importPreview); ?> sản phẩm)
                </button>
                <a href="?page=products&action=import" class="btn btn-secondary" style="flex:1;">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </section>
    <?php endif; ?>
</main>
