<?php
/**
 * Seed script: Import Vietnam province/district/ward data from provinces.open-api.vn
 * 
 * Usage: php _scripts/seed_address_data.php
 * Or access: api_get_addresses.php?action=seed
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Database.php';

use App\Core\Database as Database;

echo "=== Seed Vietnam Address Data ===\n\n";

$db = Database::getInstance();

// Kiểm tra xem đã có dữ liệu chưa
$check = $db->query("SELECT COUNT(*) FROM vietnam_provinces")->fetchColumn();
if ($check > 0) {
    echo "⚠️  Dữ liệu đã tồn tại: {$check} tỉnh/thành.\n";
    echo "   Chạy lại sau khi xóa dữ liệu cũ nếu cần.\n";
    exit;
}

// Tạo bảng nếu chưa có
$db->exec("
    CREATE TABLE IF NOT EXISTS `vietnam_provinces` (
        `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `province_id` INT UNSIGNED NOT NULL,
        `name`        VARCHAR(100) NOT NULL,
        `slug`        VARCHAR(120) NOT NULL DEFAULT '',
        `type`        VARCHAR(30)  NOT NULL DEFAULT 'tinh',
        UNIQUE KEY `idx_province_id` (`province_id`),
        KEY `idx_name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
$db->exec("
    CREATE TABLE IF NOT EXISTS `vietnam_districts` (
        `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `district_id` INT UNSIGNED NOT NULL,
        `name`        VARCHAR(100) NOT NULL,
        `slug`        VARCHAR(120) NOT NULL DEFAULT '',
        `type`        VARCHAR(30)  NOT NULL DEFAULT 'quan',
        `province_id` INT UNSIGNED NOT NULL,
        KEY `idx_province_id` (`province_id`),
        UNIQUE KEY `idx_district_id` (`district_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
$db->exec("
    CREATE TABLE IF NOT EXISTS `vietnam_wards` (
        `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `ward_id`   INT UNSIGNED NOT NULL,
        `name`      VARCHAR(100) NOT NULL,
        `slug`      VARCHAR(120) NOT NULL DEFAULT '',
        `type`      VARCHAR(30)  NOT NULL DEFAULT 'phuong',
        `district_id` INT UNSIGNED NOT NULL,
        KEY `idx_district_id` (`district_id`),
        UNIQUE KEY `idx_ward_id` (`ward_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ── Helper: fetch JSON with cURL ──
function apiFetch($url, $timeout = 30) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'BanLinhKien/1.0',
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || empty($resp)) {
        throw new Exception("API error (HTTP {$code}): {$url}");
    }
    $data = json_decode($resp, true);
    if ($data === null) {
        throw new Exception("Invalid JSON from API: {$url}");
    }
    return $data;
}

// ── Step 1: Lấy danh sách tỉnh/thành ──
echo "📡 Đang tải danh sách tỉnh/thành...\n";
$provinces = apiFetch('https://provinces.open-api.vn/api/v1/p/');
echo "✅ Đã tải " . count($provinces) . " tỉnh/thành.\n\n";

// ── Step 2: Lấy tất cả quận/huyện ──
echo "📡 Đang tải danh sách quận/huyện...\n";
$allDistricts = apiFetch('https://provinces.open-api.vn/api/v1/d/');
echo "✅ Đã tải " . count($allDistricts) . " quận/huyện.\n\n";

// ── Step 3: Lấy tất cả phường/xã ──
echo "📡 Đang tải danh sách phường/xã...\n";
$allWards = apiFetch('https://provinces.open-api.vn/api/v1/w/');
echo "✅ Đã tải " . count($allWards) . " phường/xã.\n\n";

// ── Group wards by district_code ──
$wardsByDistrict = [];
foreach ($allWards as $w) {
    $did = (int)$w['district_code'];
    if (!isset($wardsByDistrict[$did])) {
        $wardsByDistrict[$did] = [];
    }
    $wardsByDistrict[$did][] = $w;
}

// ── Group districts by province_code ──
$districtsByProvince = [];
foreach ($allDistricts as $d) {
    $pid = (int)$d['province_code'];
    if (!isset($districtsByProvince[$pid])) {
        $districtsByProvince[$pid] = [];
    }
    $districtsByProvince[$pid][] = $d;
}

echo "⏳ Đang insert vào database...\n\n";

$totalProvinces = 0;
$totalDistricts = 0;
$totalWards     = 0;

$db->beginTransaction();

$stmtProvince = $db->prepare("INSERT INTO vietnam_provinces (province_id, name, slug, type) VALUES (?, ?, ?, ?)");
$stmtDistrict = $db->prepare("INSERT INTO vietnam_districts (district_id, name, slug, type, province_id) VALUES (?, ?, ?, ?, ?)");
$stmtWard     = $db->prepare("INSERT INTO vietnam_wards (ward_id, name, slug, type, district_id) VALUES (?, ?, ?, ?, ?)");

foreach ($provinces as $p) {
    $pid   = (int)$p['code'];
    $pName = $p['name'];
    $pSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $pName), '-'));
    $pType = $p['division_type'] ?? 'tinh';

    $stmtProvince->execute([$pid, $pName, $pSlug, $pType]);
    $totalProvinces++;

    // Districts for this province
    $districts = $districtsByProvince[$pid] ?? [];
    foreach ($districts as $d) {
        $did   = (int)$d['code'];
        $dName = $d['name'];
        $dSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $dName), '-'));
        $dType = $d['division_type'] ?? 'quan';

        $stmtDistrict->execute([$did, $dName, $dSlug, $dType, $pid]);
        $totalDistricts++;

        // Wards for this district
        $wards = $wardsByDistrict[$did] ?? [];
        foreach ($wards as $w) {
            $wid   = (int)$w['code'];
            $wName = $w['name'];
            $wSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $wName), '-'));
            $wType = $w['division_type'] ?? 'phuong';

            $stmtWard->execute([$wid, $wName, $wSlug, $wType, $did]);
            $totalWards++;
        }
    }
}

$db->commit();

echo "✅ Import thành công!\n";
echo "   • {$totalProvinces} tỉnh/thành phố\n";
echo "   • {$totalDistricts} quận/huyện\n";
echo "   • {$totalWards} phường/xã\n";
echo "\n👉 Bạn có thể truy cập api_get_addresses.php?action=seed để import lại nếu cần.\n";
