<?php

class SettingsController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAllSettings() {
        $rows = $this->db->query("SELECT setting_key, setting_value FROM shop_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        return $rows;
    }

    public function getSetting($key, $default = '') {
        $stmt = $this->db->prepare("SELECT setting_value FROM shop_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public function updateSettings($data) {
        $stmt = $this->db->prepare("INSERT INTO shop_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($data as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        return true;
    }

    public function uploadLogo($file) {
        $dir = dirname(__DIR__, 2) . '/public/img/';
        return UploadHelper::storeImage($file ?: [], $dir, 'logo_', 2 * 1024 * 1024);
    }
}
