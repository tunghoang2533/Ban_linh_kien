<?php
/**
 * API Endpoint: Cascading Address Dropdown
 * 
 * Endpoints:
 *   api_get_addresses.php?level=provinces                     → Danh sách tỉnh/thành
 *   api_get_addresses.php?level=districts&province_id=XXX     → Danh sách quận/huyện
 *   api_get_addresses.php?level=wards&district_id=XXX         → Danh sách phường/xã
 *   api_get_addresses.php?action=seed                         → Import dữ liệu từ API
 */

require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$db = Database::getInstance();
$level = trim($_GET['level'] ?? '');
$action = trim($_GET['action'] ?? '');

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

// ── Seed data from API (bulk fetch approach) ──────────────────
if ($action === 'seed') {
    try {
        // Kiểm tra xem đã có dữ liệu chưa
        $check = $db->query("SELECT COUNT(*) FROM vietnam_provinces")->fetchColumn();
        if ($check > 0) {
            echo json_encode(['ok' => true, 'message' => 'Dữ liệu đã tồn tại (' . (int)$check . ' tỉnh/thành).', 'count' => (int)$check]);
            exit;
        }

        // Step 1: Lấy danh sách tỉnh/thành
        $provinces = apiFetch('https://provinces.open-api.vn/api/v1/p/');

        // Step 2: Lấy tất cả quận/huyện
        $allDistricts = apiFetch('https://provinces.open-api.vn/api/v1/d/');

        // Step 3: Lấy tất cả phường/xã
        $allWards = apiFetch('https://provinces.open-api.vn/api/v1/w/');

        // Group wards by district_code
        $wardsByDistrict = [];
        foreach ($allWards as $w) {
            $did = (int)$w['district_code'];
            if (!isset($wardsByDistrict[$did])) {
                $wardsByDistrict[$did] = [];
            }
            $wardsByDistrict[$did][] = $w;
        }

        // Group districts by province_code
        $districtsByProvince = [];
        foreach ($allDistricts as $d) {
            $pid = (int)$d['province_code'];
            if (!isset($districtsByProvince[$pid])) {
                $districtsByProvince[$pid] = [];
            }
            $districtsByProvince[$pid][] = $d;
        }

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

            $districts = $districtsByProvince[$pid] ?? [];
            foreach ($districts as $d) {
                $did   = (int)$d['code'];
                $dName = $d['name'];
                $dSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $dName), '-'));
                $dType = $d['division_type'] ?? 'quan';

                $stmtDistrict->execute([$did, $dName, $dSlug, $dType, $pid]);
                $totalDistricts++;

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

        echo json_encode([
            'ok'      => true,
            'message' => "Đã import thành công: {$totalProvinces} tỉnh/thành, {$totalDistricts} quận/huyện, {$totalWards} phường/xã.",
            'count'   => [
                'provinces' => $totalProvinces,
                'districts' => $totalDistricts,
                'wards'     => $totalWards,
            ],
        ]);
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        echo json_encode(['ok' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// ── Get provinces ──────────────────────────────────────────────
if ($level === 'provinces') {
    $stmt = $db->query("SELECT province_id AS code, name, type FROM vietnam_provinces ORDER BY name ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── Get districts by province ──────────────────────────────────
if ($level === 'districts') {
    $provinceId = (int)($_GET['province_id'] ?? 0);
    if ($provinceId <= 0) {
        echo json_encode(['error' => 'Missing province_id']);
        exit;
    }
    $stmt = $db->prepare("SELECT district_id AS code, name, type FROM vietnam_districts WHERE province_id = ? ORDER BY name ASC");
    $stmt->execute([$provinceId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── Get wards by district ──────────────────────────────────────
if ($level === 'wards') {
    $districtId = (int)($_GET['district_id'] ?? 0);
    if ($districtId <= 0) {
        echo json_encode(['error' => 'Missing district_id']);
        exit;
    }
    $stmt = $db->prepare("SELECT ward_id AS code, name, type FROM vietnam_wards WHERE district_id = ? ORDER BY name ASC");
    $stmt->execute([$districtId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ── Invalid request ─────────────────────────────────────────────
echo json_encode(['error' => 'Invalid request. Use ?level=provinces|districts|wards or ?action=seed']);
