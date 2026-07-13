<?php
class CmsController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    // ── Static Pages ──
    public function getPages() {
        return $this->db->query("SELECT * FROM cms_pages ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPageBySlug($slug) {
        $s = $this->db->prepare("SELECT * FROM cms_pages WHERE slug=?"); $s->execute([$slug]); return $s->fetch(PDO::FETCH_ASSOC);
    }
    public function createPage($data) {
        $slug = $data['slug'] ?? preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $data['title'] ?? 'page')));
        $s = $this->db->prepare("INSERT INTO cms_pages (title, slug, content, meta_title, meta_description, is_active, sort_order) VALUES (?,?,?,?,?,?,?)");
        return $s->execute([$data['title']??'', $slug, $data['content']??'', $data['meta_title']??'', $data['meta_description']??'', isset($data['is_active'])?1:0, intval($data['sort_order']??0)]);
    }
    public function updatePage($slug, $data) {
        $s = $this->db->prepare("UPDATE cms_pages SET title=?, content=?, meta_title=?, meta_description=?, is_active=?, sort_order=?, updated_at=NOW() WHERE slug=?");
        return $s->execute([$data['title']??'', $data['content']??'', $data['meta_title']??'', $data['meta_description']??'', isset($data['is_active'])?1:0, intval($data['sort_order']??0), $slug]);
    }
    public function deletePage($id) {
        return $this->db->prepare("DELETE FROM cms_pages WHERE id=?")->execute([$id]);
    }

    // ── Blog Articles ──
    public function getArticles($status = 'all', $limit = 50) {
        $limit = max(1, min(100, (int)$limit));
        $allowedStatuses = ['draft', 'published', 'archived'];
        if ($status !== 'all' && in_array($status, $allowedStatuses, true)) {
            $stmt = $this->db->prepare("SELECT a.*, u.username as author_name FROM cms_articles a LEFT JOIN users u ON a.author_id=u.id WHERE status=? ORDER BY a.created_at DESC LIMIT ?");
            $stmt->bindValue(1, $status);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        } else {
            $stmt = $this->db->prepare("SELECT a.*, u.username as author_name FROM cms_articles a LEFT JOIN users u ON a.author_id=u.id ORDER BY a.created_at DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getArticleById($id) {
        $s = $this->db->prepare("SELECT * FROM cms_articles WHERE id=?"); $s->execute([$id]); return $s->fetch(PDO::FETCH_ASSOC);
    }
    public function createArticle($data, $authorId = null) {
        $slug = $data['slug'] ?? preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $data['title'] ?? 'article')));
        $slug = $slug . '-' . time();
        $s = $this->db->prepare("INSERT INTO cms_articles (title, slug, excerpt, content, thumbnail, category, tags, author_id, status, meta_title, meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        return $s->execute([$data['title']??'', $slug, $data['excerpt']??'', $data['content']??'', $data['thumbnail']??'', $data['category']??'', $data['tags']??'', $authorId, $data['status']??'draft', $data['meta_title']??'', $data['meta_description']??'']);
    }
    public function updateArticle($id, $data) {
        $s = $this->db->prepare("UPDATE cms_articles SET title=?, excerpt=?, content=?, thumbnail=?, category=?, tags=?, status=?, meta_title=?, meta_description=?, updated_at=NOW() WHERE id=?");
        return $s->execute([$data['title']??'', $data['excerpt']??'', $data['content']??'', $data['thumbnail']??'', $data['category']??'', $data['tags']??'', $data['status']??'draft', $data['meta_title']??'', $data['meta_description']??'', $id]);
    }
    public function deleteArticle($id) {
        return $this->db->prepare("DELETE FROM cms_articles WHERE id=?")->execute([$id]);
    }
    public function getPublishedArticles($limit = 10) {
        $limit = max(1, min(100, (int)$limit));
        $stmt = $this->db->prepare("SELECT * FROM cms_articles WHERE status='published' ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
