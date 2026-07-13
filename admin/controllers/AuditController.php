<?php
class AuditController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getLogs($filters = [], $page = 1, $perPage = 50) {
        $where = []; $params = [];
        if (!empty($filters['module'])) { $where[] = 'module = ?'; $params[] = $filters['module']; }
        if (!empty($filters['user_id'])) { $where[] = 'user_id = ?'; $params[] = $filters['user_id']; }
        if (!empty($filters['action'])) { $where[] = 'action = ?'; $params[] = $filters['action']; }
        if (!empty($filters['from'])) { $where[] = 'DATE(created_at) >= ?'; $params[] = $filters['from']; }
        if (!empty($filters['to'])) { $where[] = 'DATE(created_at) <= ?'; $params[] = $filters['to']; }
        $whereStr = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $page = max(1, (int)$page);
        $perPage = max(1, min(200, (int)$perPage));
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM audit_logs$whereStr ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $idx => $value) {
            $stmt->bindValue($idx + 1, $value);
        }
        $stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countLogs($filters = []) {
        $where = []; $params = [];
        if (!empty($filters['module'])) { $where[] = 'module = ?'; $params[] = $filters['module']; }
        if (!empty($filters['user_id'])) { $where[] = 'user_id = ?'; $params[] = $filters['user_id']; }
        if (!empty($filters['action'])) { $where[] = 'action = ?'; $params[] = $filters['action']; }
        if (!empty($filters['from'])) { $where[] = 'DATE(created_at) >= ?'; $params[] = $filters['from']; }
        if (!empty($filters['to'])) { $where[] = 'DATE(created_at) <= ?'; $params[] = $filters['to']; }
        $whereStr = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM audit_logs$whereStr");
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getModules() {
        return $this->db->query("SELECT DISTINCT module FROM audit_logs ORDER BY module")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAdmins() {
        return $this->db->query("SELECT DISTINCT user_id, username FROM audit_logs WHERE user_id IS NOT NULL ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
    }
}
