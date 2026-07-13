<?php
// Admin CMS View
require_once __DIR__ . '/../../controllers/CmsController.php';
if (!isset($cmsCtrl)) $cmsCtrl = new CmsController($db);

$cmsTab      = $_GET['tab'] ?? 'pages';
$editSlug    = $_GET['edit_page'] ?? '';
$editArtId   = intval($_GET['edit_article'] ?? 0);
$pages       = $cmsCtrl->getPages();
$articles    = $cmsCtrl->getArticles();
$editPage    = $editSlug ? $cmsCtrl->getPageBySlug($editSlug) : null;
$editArticle = $editArtId ? $cmsCtrl->getArticleById($editArtId) : null;
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 class="page-title"><i class="fas fa-file-alt" style="color:#16a34a;"></i> Quản lý nội dung (CMS)</h1>
</div>

<!-- Tabs -->
<div style="border-bottom:2px solid #e2e8f0;margin-bottom:24px;display:flex;gap:4px;">
    <?php foreach (['pages'=>['fa-file','Trang tĩnh'],'articles'=>['fa-newspaper','Bài viết']] as $tab => [$icon,$label]): ?>
    <a href="?page=cms&tab=<?php echo $tab; ?>" style="padding:10px 20px;border-radius:8px 8px 0 0;text-decoration:none;display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;<?php echo $cmsTab===$tab ? 'background:#16a34a;color:white;' : 'color:var(--text-muted);'; ?>">
        <i class="fas <?php echo $icon; ?>"></i><?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (!empty($successMessage)): ?>
<div style="background:rgba(34,197,94,0.12);border:1px solid #6ee7b7;color:#4ade80;padding:12px 18px;border-radius:10px;margin-bottom:20px;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<?php if ($cmsTab === 'pages'): ?>
<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;">
    <!-- Page List -->
    <div>
        <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);margin-bottom:16px;">
        <div class="card-body" style="padding:24px;">
            <h5 style="margin:0 0 16px;font-weight:700;"><i class="fas fa-list" style="color:#16a34a;"></i> Danh sách trang</h5>
            <?php foreach ($pages as $p): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-subtle);">
                <div>
                    <p style="margin:0;font-weight:700;font-size:14px;"><?php echo htmlspecialchars($p['title']); ?></p>
                    <p style="margin:0;font-size:12px;color:var(--text-faint);">/<code><?php echo htmlspecialchars($p['slug']); ?></code></p>
                </div>
                <div style="display:flex;gap:6px;">
                    <a href="?page=cms&tab=pages&edit_page=<?php echo $p['slug']; ?>" class="btn btn-sm" style="background:#dbeafe;color:#1d4ed8;border:none;border-radius:8px;padding:5px 10px;font-size:12px;"><i class="fas fa-edit"></i></a>
                    <a href="?page=cms&tab=pages&delete_page=<?php echo $p['id']; ?>" class="btn btn-sm" style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;border-radius:8px;padding:5px 10px;font-size:12px;" onclick="return confirm('Xóa trang này?')"><i class="fas fa-trash"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div></div>

        <!-- Create Page -->
        <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body" style="padding:24px;">
            <h5 style="margin:0 0 16px;font-weight:700;"><i class="fas fa-plus" style="color:#16a34a;"></i> Tạo trang mới</h5>
            <form method="POST">
                <div style="margin-bottom:12px;"><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Tiêu đề trang *</label>
                    <input type="text" name="title" required class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"></div>
                <div style="margin-bottom:12px;"><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Slug (URL)</label>
                    <input type="text" name="slug" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" placeholder="de-trong-tu-dong-tao"></div>
                <button type="submit" name="create_page" class="btn btn-primary" style="width:100%;border-radius:10px;padding:10px;font-weight:700;background:linear-gradient(135deg,#16a34a,#15803d);border:none;">Tạo trang</button>
            </form>
        </div></div>
    </div>

    <!-- Edit Page -->
    <?php if ($editPage): ?>
    <div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:24px;">
        <h5 style="margin:0 0 16px;font-weight:700;"><i class="fas fa-edit" style="color:#2563eb;"></i> Chỉnh sửa: <?php echo htmlspecialchars($editPage['title']); ?></h5>
        <form method="POST">
            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($editPage['slug']); ?>">
            <div style="margin-bottom:14px;"><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Tiêu đề</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($editPage['title']); ?>" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"></div>
            <div style="margin-bottom:14px;"><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Nội dung (HTML)</label>
                <textarea name="content" rows="10" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:vertical;font-family:monospace;"><?php echo htmlspecialchars($editPage['content'] ?? ''); ?></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Meta Title</label>
                    <input type="text" name="meta_title" value="<?php echo htmlspecialchars($editPage['meta_title']??''); ?>" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"></div>
                <div><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Sort Order</label>
                    <input type="number" name="sort_order" value="<?php echo $editPage['sort_order']??0; ?>" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"></div>
            </div>
            <div style="margin-bottom:18px;"><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Meta Description</label>
                <textarea name="meta_description" rows="2" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:none;"><?php echo htmlspecialchars($editPage['meta_description']??''); ?></textarea></div>
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px;cursor:pointer;font-size:13px;font-weight:600;">
                <input type="checkbox" name="is_active" value="1" <?php echo ($editPage['is_active']??1)?'checked':''; ?> style="width:16px;height:16px;accent-color:#16a34a;"> Hiển thị công khai
            </label>
            <button type="submit" name="save_page" class="btn btn-primary" style="width:100%;border-radius:10px;padding:11px;font-weight:700;background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;"><i class="fas fa-save"></i> Lưu trang</button>
        </form>
    </div></div>
    <?php else: ?>
    <div style="display:flex;align-items:center;justify-content:center;background:var(--bg-surface);border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);color:var(--text-faint);min-height:200px;"><div style="text-align:center;"><i class="fas fa-arrow-left" style="font-size:28px;display:block;margin-bottom:8px;"></i>Chọn một trang để chỉnh sửa</div></div>
    <?php endif; ?>
</div>

<?php else: // articles ?>
<div style="margin-bottom:16px;"><button onclick="document.getElementById('form-new-article').style.display=document.getElementById('form-new-article').style.display==='none'?'block':'none'" class="btn btn-primary" style="background:linear-gradient(135deg,#16a34a,#15803d);border:none;border-radius:12px;padding:10px 20px;font-weight:700;"><i class="fas fa-plus"></i> Thêm bài viết mới</button></div>

<div id="form-new-article" style="display:none;margin-bottom:20px;">
<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
<div class="card-body" style="padding:24px;">
    <h5 style="margin:0 0 20px;font-weight:700;"><i class="fas fa-pen" style="color:#16a34a;"></i> Bài viết mới</h5>
    <form method="POST" enctype="multipart/form-data">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:14px;">
            <div><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Tiêu đề *</label>
                <input type="text" name="title" required class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;"></div>
            <div><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Danh mục</label>
                <input type="text" name="category" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" placeholder="Tin tức, Hướng dẫn..."></div>
        </div>
        <div style="margin-bottom:14px;"><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Tóm tắt</label>
            <textarea name="excerpt" rows="2" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:none;"></textarea></div>
        <div style="margin-bottom:14px;"><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Nội dung (HTML)</label>
            <textarea name="content" rows="8" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;resize:vertical;"></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:18px;">
            <div><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Ảnh đại diện</label>
                <input type="file" name="thumbnail" accept="image/*" style="font-size:12px;"></div>
            <div><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Tags</label>
                <input type="text" name="tags" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;" placeholder="pc, build, laptop"></div>
            <div><label style="font-size:13px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:5px;">Trạng thái</label>
                <select name="status" class="form-control" style="border-radius:10px;border:1px solid var(--border-muted);padding:10px;">
                    <option value="draft">Bản nháp</option><option value="published">Đăng ngay</option>
                </select></div>
        </div>
        <button type="submit" name="create_article" class="btn btn-primary" style="border-radius:10px;padding:10px 24px;font-weight:700;background:linear-gradient(135deg,#16a34a,#15803d);border:none;"><i class="fas fa-save"></i> Lưu bài viết</button>
    </form>
</div></div>
</div>

<!-- Articles Table -->
<div class="card" style="border-radius:16px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
<div class="card-body" style="padding:0;">
<table style="width:100%;border-collapse:collapse;">
    <thead><tr style="background:var(--bg-elevated);">
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);text-align:left;">TIÊU ĐỀ</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">DANH MỤC</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">TÁC GIẢ</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">NGÀY TẠO</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);">TRẠNG THÁI</th>
        <th style="padding:14px 16px;font-size:12px;font-weight:700;color:var(--text-muted);"></th>
    </tr></thead>
    <tbody>
    <?php if (empty($articles)): ?>
    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-faint);">Chưa có bài viết nào</td></tr>
    <?php else: foreach ($articles as $a): ?>
    <tr style="border-bottom:1px solid var(--border-subtle);">
        <td style="padding:14px 16px;font-weight:700;color:var(--text-primary);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($a['title']); ?></td>
        <td style="padding:14px 16px;font-size:13px;"><?php echo htmlspecialchars($a['category'] ?? '—'); ?></td>
        <td style="padding:14px 16px;font-size:13px;"><?php echo htmlspecialchars($a['author_name'] ?? '—'); ?></td>
        <td style="padding:14px 16px;font-size:13px;color:var(--text-muted);"><?php echo date('d/m/Y', strtotime($a['created_at'])); ?></td>
        <td style="padding:14px 16px;">
            <span style="padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;<?php echo $a['status']==='published'?'background:rgba(34,197,94,0.12);color:#4ade80;':'background:rgba(245,158,11,0.12);color:#fbbf24;'; ?>">
                <?php echo $a['status']==='published'?'Đã đăng':'Bản nháp'; ?>
            </span>
        </td>
        <td style="padding:14px 16px;white-space:nowrap;">
            <a href="?page=cms&tab=articles&edit_article=<?php echo $a['id']; ?>" class="btn btn-sm" style="background:#dbeafe;color:#1d4ed8;border:none;border-radius:8px;padding:5px 10px;font-size:12px;margin-right:4px;"><i class="fas fa-edit"></i></a>
            <a href="?page=cms&tab=articles&delete_article=<?php echo $a['id']; ?>" class="btn btn-sm" style="background:rgba(239,68,68,0.12);color:#dc2626;border:none;border-radius:8px;padding:5px 10px;font-size:12px;" onclick="return confirm('Xóa bài viết này?')"><i class="fas fa-trash"></i></a>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div>
<?php endif; ?>
