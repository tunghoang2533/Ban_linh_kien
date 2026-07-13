<?php
/**
 * AdminController — Facade delegating to specialized admin sub-controllers.
 *
 * @method mixed getDashboardStats()
 * @method mixed getRevenueByMonth(int $months = 12)
 * @method mixed getOrdersByStatus()
 * @method mixed getTopProducts(int $limit = 8)
 * @method mixed getMonthComparison()
 * @method mixed getLowStockProducts(int $limit = 5)
 * @method mixed getLowStockCount()
 * @method mixed getProductAlertMap(int $days = 365, int $returnThreshold = 3)
 * @method mixed getSlowMovingCount(int $days = 365)
 * @method mixed getHighReturnCount(int $threshold = 3)
 *
 * @method mixed getProducts()
 * @method mixed getProductById(int $id)
 * @method int|bool addProduct(array $data)
 * @method bool updateProduct(int $id, array $data)
 * @method bool deleteProduct(int $id)
 * @method bool toggleProductStatus(int $id)
 * @method bool addProductImages(int $productId, array $images)
 * @method array getProductImages(int $productId)
 * @method string|false deleteProductImage(int $imageId)
 * @method array getProductsByCategory(int $categoryId)
 *
 * @method mixed getOrders()
 * @method mixed getOrdersFiltered(array $filters = [])
 * @method mixed getOrderById(int $id)
 * @method mixed getOrderCountByStatus()
 *
 * @method mixed getUsers()
 * @method mixed getUserById(int $id)
 * @method mixed getUserStats(int $id)
 * @method mixed getUserOrders(int $id)
 * @method bool blockUser(int $id, string $reason = '')
 * @method bool unblockUser(int $id)
 *
 * @method mixed getAllCategories()
 * @method mixed getAllBrands()
 * @method mixed getCategoryById(int $id)
 * @method mixed getBrandById(int $id)
 * @method int|bool createCategory(string $name, string $slug = '')
 * @method bool updateCategory(int $id, string $name, string $slug = '')
 * @method bool deleteCategory(int $id)
 * @method int|bool createBrand(string $name, string $image = '')
 * @method bool updateBrand(int $id, string $name, string $image = '')
 * @method bool deleteBrand(int $id)
 *
 * @method mixed getAllVouchers()
 * @method mixed getVoucherStats()
 * @method bool deleteVoucher(int $id)
 * @method bool toggleVoucherStatus(int $id)
 * @method int|bool createVoucher(array $data)
 * @method bool updateVoucher(int $id, array $data)
 * @method mixed getPersonalVouchers()
 * @method mixed getAllUsers()
 * @method int countPersonalVouchers()
 *
 * @method mixed getInventory(string $filter = 'all', string $search = '', int $warehouseId = null)
 * @method mixed getInventoryStats(int $warehouseId = null)
 * @method mixed getWarehouses()
 * @method mixed getReceipts(...$args)
 * @method int countReceipts(...$args)
 * @method mixed getReceiptById(int $id)
 * @method mixed getReceiptItems(int $id)
 * @method array createReceipt(array $data, array $items, int $createdBy)
 * @method bool submitReceipt(int $id, int $userId)
 * @method bool approveReceipt(int $id, int $userId)
 * @method bool cancelReceipt(int $id, int $userId, string $reason = '')
 * @method mixed getPOs(...$args)
 * @method mixed getPOStats()
 * @method array createPO(array $data, array $items, int $createdBy)
 * @method bool submitPO(int $id, int $userId)
 * @method bool approvePO(int $id, int $userId)
 * @method bool markPOOrdered(int $id)
 * @method bool receivePO(int $id, int $userId)
 * @method bool cancelPO(int $id, int $userId, string $reason = '')
 * @method mixed getStocktakeSessions(...$args)
 * @method mixed getWarehouseLogs(...$args)
 * @method int countWarehouseLogs(...$args)
 * @method mixed getWarehouseLogById(int $id)
 * @method mixed getAllProductsWithDiscount()
 * @method bool updateDiscount(int $id, float $discount)
 * @method mixed getBanners()
 * @method mixed getActiveBanners()
 */
class AdminController {
    private $db;
    private $dashboardController;
    private $productController;
    private $orderController;
    private $userController;
    private $categoryController;
    private $voucherController;
    private $inventoryController;
    private $bannerController;

    public function __construct($db) {
        $this->db = $db;
        require_once __DIR__ . '/DashboardController.php';
        require_once __DIR__ . '/ProductController.php';
        require_once __DIR__ . '/OrderController.php';
        require_once __DIR__ . '/UserController.php';
        require_once __DIR__ . '/CategoryController.php';
        require_once __DIR__ . '/VoucherAdminController.php';
        require_once __DIR__ . '/InventoryController.php';
        require_once __DIR__ . '/BannerController.php';

        $this->dashboardController = new DashboardController($db);
        $this->productController   = new ProductController($db);
        $this->orderController     = new OrderController($db);
        $this->userController      = new UserController($db);
        $this->categoryController  = new CategoryController($db);
        $this->voucherController   = new VoucherAdminController($db);
        $this->inventoryController = new InventoryController($db);
        $this->bannerController    = new BannerController($db);
    }

    /**
     * Magic call handler — delegates to the appropriate sub-controller.
     * 
     * @param string $name Method name
     * @param array  $arguments Arguments to pass
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($name, $arguments) {
        $controllers = [
            $this->dashboardController,
            $this->productController,
            $this->orderController,
            $this->userController,
            $this->categoryController,
            $this->voucherController,
            $this->inventoryController,
            $this->bannerController,
        ];

        foreach ($controllers as $controller) {
            if (method_exists($controller, $name)) {
                $result = call_user_func_array([$controller, $name], $arguments);
                // Ghi log debug để dễ trace (chỉ khi APP_DEBUG bật)
                if (defined('APP_DEBUG') && APP_DEBUG) {
                    Logger::debug("AdminController delegation", [
                        'method' => $name,
                        'target' => get_class($controller),
                        'args'   => count($arguments),
                    ]);
                }
                return $result;
            }
        }

        Logger::warning("AdminController: method not found", ['method' => $name]);
        throw new BadMethodCallException("Method {$name} does not exist in AdminController.");
    }
}
