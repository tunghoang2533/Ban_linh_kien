<?php
require_once 'config.php';
require_once 'core/Database.php';
require_once 'admin/controllers/ShippingAdminController.php';

header('Content-Type: application/json');
$db   = Database::getInstance();
$ctrl = new ShippingAdminController($db);

$province = trim($_POST['province'] ?? '');
$total    = floatval($_POST['total'] ?? 0);

echo json_encode(['fee' => $ctrl->calculateFee($province, $total), 'formatted' => number_format($ctrl->calculateFee($province, $total), 0, ',', '.') . 'đ']);
