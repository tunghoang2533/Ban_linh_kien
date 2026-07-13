<?php
/**
 * Handler: CMS — Quản lý trang tĩnh & bài viết
 */
$cmsCtrl = new CmsController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_page'])) { $cmsCtrl->updatePage($_POST['slug'], $_POST); $successMessage = 'Đã lưu trang!'; }
    if (isset($_POST['create_page'])) { $cmsCtrl->createPage($_POST); $successMessage = 'Đã tạo trang mới!'; }
    if (isset($_POST['save_article'])) { $cmsCtrl->updateArticle(intval($_POST['article_id']), $_POST); $successMessage = 'Đã lưu bài viết!'; }
    if (isset($_POST['create_article'])) {
        $uploadDir = $projectRoot . '/public/img/news/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $thumb = '';
        if (!empty($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $uploadedThumb = UploadHelper::storeImage($_FILES['thumbnail'], $uploadDir, 'news_', 2 * 1024 * 1024, ['image/jpeg', 'image/png', 'image/webp']);
            if ($uploadedThumb) $thumb = $uploadedThumb;
        }
        $_POST['thumbnail'] = $thumb;
        $cmsCtrl->createArticle($_POST, $_SESSION['user_id'] ?? null);
        $successMessage = 'Đã thêm bài viết mới!';
    }
}
if (isset($_GET['delete_page'])) { $cmsCtrl->deletePage(intval($_GET['delete_page'])); header('Location: ?page=cms'); exit; }
if (isset($_GET['delete_article'])) { $cmsCtrl->deleteArticle(intval($_GET['delete_article'])); header('Location: ?page=cms'); exit; }

include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/cms/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
