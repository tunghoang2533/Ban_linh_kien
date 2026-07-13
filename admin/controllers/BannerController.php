<?php

class BannerController {
    private $db;
    private $uploadDir;

    public function __construct($db) {
        $this->db = $db;
        $this->uploadDir = dirname(dirname(dirname(__FILE__))) . '/public/img/banners/';
        if (!is_dir($this->uploadDir)) mkdir($this->uploadDir, 0755, true);
    }

    /** Lấy tất cả banners, sắp xếp theo sort_order */
    public function getAllBanners() {
        $stmt = $this->db->query("SELECT * FROM banners ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy banners đang active (cho trang người dùng) */
    public function getActiveBanners() {
        $stmt = $this->db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy 1 banner theo ID */
    public function getBannerById($id) {
        $stmt = $this->db->prepare("SELECT * FROM banners WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Thêm banner mới, trả về ID hoặc false */
    public function addBanner($data, $file = null) {
        $image = $this->handleUpload($file);
        if (!$image) return ['success' => false, 'message' => 'Vui lòng chọn ảnh banner hợp lệ (JPG, PNG, WEBP, GIF).'];

        $stmt = $this->db->prepare(
            "INSERT INTO banners (title, subtitle, tag, btn_text, btn_url, accent_color, image, sort_order, is_active)
             VALUES (:title, :subtitle, :tag, :btn_text, :btn_url, :accent, :image, :sort, :active)"
        );
        $ok = $stmt->execute([
            ':title'    => trim($data['title']    ?? ''),
            ':subtitle' => trim($data['subtitle'] ?? ''),
            ':tag'      => trim($data['tag']      ?? ''),
            ':btn_text' => trim($data['btn_text'] ?? 'Xem ngay'),
            ':btn_url'  => trim($data['btn_url']  ?? ''),
            ':accent'   => trim($data['accent_color'] ?? '#6366f1'),
            ':image'    => $image,
            ':sort'     => (int)($data['sort_order'] ?? 0),
            ':active'   => isset($data['is_active']) ? 1 : 0,
        ]);
        return $ok ? ['success' => true, 'id' => $this->db->lastInsertId()] : ['success' => false, 'message' => 'Lỗi khi lưu banner.'];
    }

    /** Cập nhật banner (ảnh tùy chọn) */
    public function updateBanner($id, $data, $file = null) {
        $existing = $this->getBannerById($id);
        if (!$existing) return ['success' => false, 'message' => 'Không tìm thấy banner.'];

        $image = $existing['image']; // giữ ảnh cũ mặc định
        if ($file && $file['error'] === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {
            $newImage = $this->handleUpload($file);
            if (!$newImage) return ['success' => false, 'message' => 'Ảnh không hợp lệ (JPG, PNG, WEBP, GIF).'];
            // Xóa ảnh cũ nếu không phải banner mặc định
            $oldPath = $this->uploadDir . $existing['image'];
            if (!in_array($existing['image'], ['banner1.png','banner2.png','banner3.png']) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $image = $newImage;
        }

        $stmt = $this->db->prepare(
            "UPDATE banners SET title=:title, subtitle=:subtitle, tag=:tag, btn_text=:btn_text,
             btn_url=:btn_url, accent_color=:accent, image=:image, sort_order=:sort, is_active=:active
             WHERE id=:id"
        );
        $ok = $stmt->execute([
            ':title'    => trim($data['title']    ?? ''),
            ':subtitle' => trim($data['subtitle'] ?? ''),
            ':tag'      => trim($data['tag']      ?? ''),
            ':btn_text' => trim($data['btn_text'] ?? 'Xem ngay'),
            ':btn_url'  => trim($data['btn_url']  ?? ''),
            ':accent'   => trim($data['accent_color'] ?? '#6366f1'),
            ':image'    => $image,
            ':sort'     => (int)($data['sort_order'] ?? 0),
            ':active'   => isset($data['is_active']) ? 1 : 0,
            ':id'       => (int)$id,
        ]);
        return $ok ? ['success' => true] : ['success' => false, 'message' => 'Lỗi khi cập nhật banner.'];
    }

    /** Xóa banner và file ảnh */
    public function deleteBanner($id) {
        $banner = $this->getBannerById($id);
        if (!$banner) return false;

        $stmt = $this->db->prepare("DELETE FROM banners WHERE id = :id");
        $ok = $stmt->execute([':id' => (int)$id]);

        // Xóa file ảnh (trừ banner mặc định)
        if ($ok && !in_array($banner['image'], ['banner1.png','banner2.png','banner3.png'])) {
            $path = $this->uploadDir . $banner['image'];
            if (file_exists($path)) @unlink($path);
        }
        return $ok;
    }

    /** Bật/tắt trạng thái banner */
    public function toggleBannerStatus($id) {
        $stmt = $this->db->prepare("UPDATE banners SET is_active = IF(is_active=1,0,1) WHERE id=:id");
        return $stmt->execute([':id' => (int)$id]);
    }

    /** Cập nhật thứ tự sort_order (nhận mảng [id => order]) */
    public function updateSortOrder($orders) {
        $stmt = $this->db->prepare("UPDATE banners SET sort_order=:ord WHERE id=:id");
        foreach ($orders as $id => $ord) {
            $stmt->execute([':ord' => (int)$ord, ':id' => (int)$id]);
        }
        return true;
    }

    // ── Private helpers ──

    private function handleUpload($file) {
        return UploadHelper::storeImage($file ?: [], $this->uploadDir, 'banner_', 4 * 1024 * 1024);
    }
}
?>
