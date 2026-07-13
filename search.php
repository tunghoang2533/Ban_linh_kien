<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Models\ProductModel;

$db           = (new Database())->connect();
$productModel = new ProductModel($db);

// Lấy tham số tìm kiếm từ URL
$keyword    = trim($_GET['key']        ?? '');
$categoryId = (int)($_GET['cat']       ?? 0);
$brandId    = (int)($_GET['brand']     ?? 0);
$priceMin   = (float)($_GET['pmin']    ?? 0);
$priceMax   = (float)($_GET['pmax']    ?? 0);
$sortBy     = in_array($_GET['sort'] ?? '', ['newest','price_asc','price_desc','bestsell','rating'])
              ? $_GET['sort'] : 'newest';
$page       = max(1, (int)($_GET['page'] ?? 1));

// Thực hiện tìm kiếm
$result = $productModel->search($keyword, [
    'category_id' => $categoryId,
    'brand_id'    => $brandId,
    'price_min'   => $priceMin,
    'price_max'   => $priceMax,
    'sort'        => $sortBy,
    'page'        => $page,
    'per_page'    => 20,
]);

$products    = $result['products'];
$total       = $result['total'];
$totalPages  = $result['total_pages'];

// Lấy danh sách categories và brands cho bộ lọc
$categories = $productModel->getAllCategories();
$brands     = $productModel->getAllBrands();

include 'app/views/header.php';
include 'app/views/products/search_view.php';
include 'app/views/footer.php';
?>
