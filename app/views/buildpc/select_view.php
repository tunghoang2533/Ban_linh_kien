<div class="container" style="margin-top: 30px; margin-bottom: 50px;">
    <?php
        $cat_name = $cat_name ?? 'Linh kiện';
        $cat_id = $cat_id ?? 0;
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #288ad6; padding-bottom: 10px; margin-bottom: 20px;">
        <h2>Chọn: <span style="color: #288ad6;"><?php echo $cat_name; ?></span></h2>
        
        <?php if(!empty($required_socket)): ?>
            <span style="background: #ffc107; color: #333; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px;">
                <i class="fa fa-filter"></i> Đang lọc theo chuẩn: <?php echo $required_socket; ?>
            </span>
        <?php endif; ?>

        <a href="buildpc.php" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px;">
            Quay lại cấu hình
        </a>
    </div>

    <ul style="display: flex; flex-wrap: wrap; gap: 20px; padding: 0; list-style: none;">
        <?php if(!empty($products)): ?>
            <?php foreach($products as $row): ?>
                <li style="width: 250px; border: 1px solid #eee; padding: 15px; background: #fff; border-radius: 8px; text-align: center; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <?php
                        $defaultImage = 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" fill="%23f3f3f3"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%23999" font-size="24">No image</text></svg>';
                        if (strpos($row['image'], 'data:') === 0) {
                            $productImageSrc = $row['image'];
                        } elseif (!empty($row['image']) && file_exists(__DIR__ . '/../../public/img/products/' . $row['image'])) {
                            $productImageSrc = BASE_URL . 'public/img/products/' . $row['image'];
                        } else {
                            $productImageSrc = $defaultImage;
                        }
                    ?>
                    <img src="<?php echo $productImageSrc; ?>" style="width: 100%; height: 180px; object-fit: contain; margin-bottom: 10px;" loading="lazy">
                    
                    <h3 style="font-size: 15px; color: #333; height: 40px; overflow: hidden; margin: 10px 0;">
                        <?php echo $row['name']; ?>
                    </h3>
                    
                    <div style="height: 25px; margin-bottom: 10px;">
                        <?php if(!empty($row['socket'])): ?>
                            <span style="background: #e9ecef; color: #495057; padding: 3px 8px; border-radius: 4px; font-size: 12px;">
                                Socket: <?php echo $row['socket']; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div style="color: #e10c00; font-weight: bold; font-size: 18px; margin-bottom: 15px;">
                        <?php echo number_format($row['price'], 0, ',', '.'); ?> ₫
                    </div>
                    
                    <a href="buildpc.php?action=add&cat_id=<?php echo $cat_id; ?>&product_id=<?php echo $row['id']; ?>" style="display: block; background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 14px;">
                        CHỌN SẢN PHẨM NÀY
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="width: 100%; text-align: center; padding: 50px; color: #dc3545; background: #f8d7da; border-radius: 8px;">
                <h4 style="margin: 0;">Không tìm thấy linh kiện phù hợp!</h4>
                <p style="margin-top: 10px; color: #666;">Hệ thống đã lọc các sản phẩm không tương thích. Vui lòng thử chọn CPU hoặc Mainboard khác.</p>
            </div>
        <?php endif; ?>
    </ul>
</div>