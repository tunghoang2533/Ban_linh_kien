<?php
require_once 'config.php';
require_once 'core/Database.php';
$db = Database::getInstance();

$added = 0;
$errors = [];

// Helper: thêm sp + specs
function addProduct($db, $data, $specs = []) {
    global $added, $errors;
    try {
        $sql = "INSERT INTO products (category_id, brand_id, name, price, discount_percent, quantity, image, description, is_featured, is_active, created_at)
                VALUES (:cat, :brand, :name, :price, :disc, :qty, '', :desc, :feat, 1, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':cat'   => $data['cat'],
            ':brand' => $data['brand'],
            ':name'  => $data['name'],
            ':price' => $data['price'],
            ':disc'  => $data['disc'] ?? 0,
            ':qty'   => $data['qty'] ?? 50,
            ':desc'  => $data['desc'] ?? '',
            ':feat'  => $data['feat'] ?? 0,
        ]);
        $pid = $db->lastInsertId();
        foreach ($specs as $sname => $sval) {
            $s = $db->prepare("INSERT INTO product_specs (product_id, spec_name, spec_value) VALUES (?,?,?)");
            $s->execute([$pid, $sname, $sval]);
        }
        $added++;
        echo "✅ [{$data['cat']}] {$data['name']}\n";
    } catch(Exception $e) {
        $errors[] = $data['name'] . ': ' . $e->getMessage();
        echo "❌ {$data['name']}: {$e->getMessage()}\n";
    }
}

// Thêm brand mới nếu cần
function ensureBrand($db, $name) {
    $r = $db->prepare("SELECT id FROM brands WHERE name=?");
    $r->execute([$name]);
    $row = $r->fetch();
    if ($row) return $row['id'];
    $db->prepare("INSERT INTO brands (name) VALUES (?)")->execute([$name]);
    return $db->lastInsertId();
}

$nzxt   = ensureBrand($db, 'NZXT');
$lian   = ensureBrand($db, 'Lian Li');
$fractal= ensureBrand($db, 'Fractal Design');
$be     = ensureBrand($db, 'be quiet!');
$evga   = ensureBrand($db, 'EVGA');
$deepcool = ensureBrand($db, 'DeepCool');
$crucial  = ensureBrand($db, 'Crucial');
$gskill   = ensureBrand($db, 'G.Skill');
$sapphire = ensureBrand($db, 'Sapphire');
$asrock   = ensureBrand($db, 'ASRock');
$pny      = ensureBrand($db, 'PNY');
$thermalt = ensureBrand($db, 'Thermaltake');

echo "=== CPU (cat_id=1) ===\n";
// Intel LGA1700
addProduct($db, ['cat'=>1,'brand'=>1,'name'=>'Intel Core i3-12100F','price'=>2490000,'disc'=>5,'qty'=>80,'desc'=>'CPU Intel Core i3-12100F, 4 nhân 8 luồng, 3.3GHz-4.3GHz, TDP 58W, Socket LGA1700','feat'=>0],
    ['Socket'=>'LGA1700','Số nhân/luồng'=>'4/8','Xung nhịp'=>'3.3 - 4.3 GHz','TDP'=>'58W','Thế hệ'=>'Alder Lake 12th']);

addProduct($db, ['cat'=>1,'brand'=>1,'name'=>'Intel Core i5-12400F','price'=>3890000,'disc'=>0,'qty'=>60,'desc'=>'CPU Intel Core i5-12400F, 6 nhân 12 luồng, 2.5GHz-4.4GHz, Socket LGA1700','feat'=>1],
    ['Socket'=>'LGA1700','Số nhân/luồng'=>'6/12','Xung nhịp'=>'2.5 - 4.4 GHz','TDP'=>'65W','Thế hệ'=>'Alder Lake 12th']);

addProduct($db, ['cat'=>1,'brand'=>1,'name'=>'Intel Core i5-13400F','price'=>4590000,'disc'=>0,'qty'=>50,'desc'=>'CPU Intel Core i5-13400F, 10 nhân 16 luồng, 2.5GHz-4.6GHz, Socket LGA1700','feat'=>1],
    ['Socket'=>'LGA1700','Số nhân/luồng'=>'10/16','Xung nhịp'=>'2.5 - 4.6 GHz','TDP'=>'65W','Thế hệ'=>'Raptor Lake 13th']);

addProduct($db, ['cat'=>1,'brand'=>1,'name'=>'Intel Core i7-12700F','price'=>6990000,'disc'=>0,'qty'=>30,'desc'=>'CPU Intel Core i7-12700F, 12 nhân 20 luồng, 2.1GHz-4.9GHz, Socket LGA1700','feat'=>0],
    ['Socket'=>'LGA1700','Số nhân/luồng'=>'12/20','Xung nhịp'=>'2.1 - 4.9 GHz','TDP'=>'65W','Thế hệ'=>'Alder Lake 12th']);

addProduct($db, ['cat'=>1,'brand'=>1,'name'=>'Intel Core i7-13700F','price'=>8490000,'disc'=>5,'qty'=>25,'desc'=>'CPU Intel Core i7-13700F, 16 nhân 24 luồng, 2.1GHz-5.2GHz, Socket LGA1700','feat'=>1],
    ['Socket'=>'LGA1700','Số nhân/luồng'=>'16/24','Xung nhịp'=>'2.1 - 5.2 GHz','TDP'=>'65W','Thế hệ'=>'Raptor Lake 13th']);

addProduct($db, ['cat'=>1,'brand'=>1,'name'=>'Intel Core i9-13900F','price'=>14990000,'disc'=>0,'qty'=>15,'desc'=>'CPU Intel Core i9-13900F, 24 nhân 32 luồng, 2.0GHz-5.6GHz, Socket LGA1700','feat'=>1],
    ['Socket'=>'LGA1700','Số nhân/luồng'=>'24/32','Xung nhịp'=>'2.0 - 5.6 GHz','TDP'=>'65W','Thế hệ'=>'Raptor Lake 13th']);

// AMD AM5
addProduct($db, ['cat'=>1,'brand'=>4,'name'=>'AMD Ryzen 5 7600X','price'=>5990000,'disc'=>10,'qty'=>40,'desc'=>'CPU AMD Ryzen 5 7600X, 6 nhân 12 luồng, 4.7GHz-5.3GHz, Socket AM5','feat'=>1],
    ['Socket'=>'AM5','Số nhân/luồng'=>'6/12','Xung nhịp'=>'4.7 - 5.3 GHz','TDP'=>'105W','Thế hệ'=>'Ryzen 7000 Zen 4']);

addProduct($db, ['cat'=>1,'brand'=>4,'name'=>'AMD Ryzen 7 7700X','price'=>7990000,'disc'=>8,'qty'=>30,'desc'=>'CPU AMD Ryzen 7 7700X, 8 nhân 16 luồng, 4.5GHz-5.4GHz, Socket AM5','feat'=>1],
    ['Socket'=>'AM5','Số nhân/luồng'=>'8/16','Xung nhịp'=>'4.5 - 5.4 GHz','TDP'=>'105W','Thế hệ'=>'Ryzen 7000 Zen 4']);

addProduct($db, ['cat'=>1,'brand'=>4,'name'=>'AMD Ryzen 9 7900X','price'=>11990000,'disc'=>0,'qty'=>20,'desc'=>'CPU AMD Ryzen 9 7900X, 12 nhân 24 luồng, 4.7GHz-5.6GHz, Socket AM5','feat'=>1],
    ['Socket'=>'AM5','Số nhân/luồng'=>'12/24','Xung nhịp'=>'4.7 - 5.6 GHz','TDP'=>'170W','Thế hệ'=>'Ryzen 7000 Zen 4']);

// AMD AM4
addProduct($db, ['cat'=>1,'brand'=>4,'name'=>'AMD Ryzen 5 5500','price'=>1990000,'disc'=>0,'qty'=>70,'desc'=>'CPU AMD Ryzen 5 5500, 6 nhân 12 luồng, 3.6GHz-4.2GHz, Socket AM4','feat'=>0],
    ['Socket'=>'AM4','Số nhân/luồng'=>'6/12','Xung nhịp'=>'3.6 - 4.2 GHz','TDP'=>'65W','Thế hệ'=>'Ryzen 5000 Zen 3']);

addProduct($db, ['cat'=>1,'brand'=>4,'name'=>'AMD Ryzen 7 5700X','price'=>3490000,'disc'=>5,'qty'=>45,'desc'=>'CPU AMD Ryzen 7 5700X, 8 nhân 16 luồng, 3.4GHz-4.6GHz, Socket AM4','feat'=>0],
    ['Socket'=>'AM4','Số nhân/luồng'=>'8/16','Xung nhịp'=>'3.4 - 4.6 GHz','TDP'=>'65W','Thế hệ'=>'Ryzen 5000 Zen 3']);

echo "\n=== MAINBOARD (cat_id=3) ===\n";
// Intel LGA1700
addProduct($db, ['cat'=>3,'brand'=>3,'name'=>'ASUS PRIME B660M-A','price'=>2990000,'disc'=>0,'qty'=>40,'desc'=>'Mainboard ASUS PRIME B660M-A, Socket LGA1700, DDR4, mATX','feat'=>0],
    ['Socket'=>'LGA1700','Chipset'=>'B660','Kích thước'=>'mATX','Khe RAM'=>'4 khe DDR4','Chuẩn RAM'=>'DDR4']);

addProduct($db, ['cat'=>3,'brand'=>7,'name'=>'MSI PRO B660M-A DDR4','price'=>2790000,'disc'=>0,'qty'=>35,'desc'=>'Mainboard MSI PRO B660M-A DDR4, Socket LGA1700, mATX','feat'=>0],
    ['Socket'=>'LGA1700','Chipset'=>'B660','Kích thước'=>'mATX','Khe RAM'=>'4 khe DDR4','Chuẩn RAM'=>'DDR4']);

addProduct($db, ['cat'=>3,'brand'=>8,'name'=>'Gigabyte B660M DS3H DDR4','price'=>2490000,'disc'=>5,'qty'=>50,'desc'=>'Mainboard Gigabyte B660M DS3H DDR4, Socket LGA1700, mATX, giá tốt','feat'=>0],
    ['Socket'=>'LGA1700','Chipset'=>'B660','Kích thước'=>'mATX','Khe RAM'=>'4 khe DDR4','Chuẩn RAM'=>'DDR4']);

addProduct($db, ['cat'=>3,'brand'=>3,'name'=>'ASUS ROG STRIX B660-F Gaming','price'=>5490000,'disc'=>0,'qty'=>20,'desc'=>'Mainboard ASUS ROG STRIX B660-F Gaming, LGA1700, ATX, DDR5, WiFi','feat'=>1],
    ['Socket'=>'LGA1700','Chipset'=>'B660','Kích thước'=>'ATX','Khe RAM'=>'4 khe DDR5','Chuẩn RAM'=>'DDR5']);

addProduct($db, ['cat'=>3,'brand'=>7,'name'=>'MSI MAG Z690 TOMAHAWK DDR4','price'=>5990000,'disc'=>0,'qty'=>18,'desc'=>'Mainboard MSI MAG Z690 TOMAHAWK DDR4, LGA1700, ATX, overclocking','feat'=>1],
    ['Socket'=>'LGA1700','Chipset'=>'Z690','Kích thước'=>'ATX','Khe RAM'=>'4 khe DDR4','Chuẩn RAM'=>'DDR4']);

// AMD AM5
addProduct($db, ['cat'=>3,'brand'=>3,'name'=>'ASUS PRIME B650-PLUS','price'=>4290000,'disc'=>0,'qty'=>25,'desc'=>'Mainboard ASUS PRIME B650-PLUS, Socket AM5, DDR5, ATX','feat'=>0],
    ['Socket'=>'AM5','Chipset'=>'B650','Kích thước'=>'ATX','Khe RAM'=>'4 khe DDR5','Chuẩn RAM'=>'DDR5']);

addProduct($db, ['cat'=>3,'brand'=>8,'name'=>'Gigabyte B650 AORUS Elite AX','price'=>5490000,'disc'=>5,'qty'=>22,'desc'=>'Mainboard Gigabyte B650 AORUS Elite AX, AM5, DDR5, WiFi 6E','feat'=>1],
    ['Socket'=>'AM5','Chipset'=>'B650','Kích thước'=>'ATX','Khe RAM'=>'4 khe DDR5','Chuẩn RAM'=>'DDR5']);

addProduct($db, ['cat'=>3,'brand'=>7,'name'=>'MSI MPG X670E Carbon WiFi','price'=>9990000,'disc'=>0,'qty'=>10,'desc'=>'Mainboard MSI MPG X670E Carbon WiFi, AM5, DDR5, ATX, high-end','feat'=>1],
    ['Socket'=>'AM5','Chipset'=>'X670E','Kích thước'=>'ATX','Khe RAM'=>'4 khe DDR5','Chuẩn RAM'=>'DDR5']);

// AMD AM4
addProduct($db, ['cat'=>3,'brand'=>8,'name'=>'Gigabyte B550M DS3H','price'=>1890000,'disc'=>0,'qty'=>60,'desc'=>'Mainboard Gigabyte B550M DS3H, Socket AM4, DDR4, mATX, giá rẻ','feat'=>0],
    ['Socket'=>'AM4','Chipset'=>'B550','Kích thước'=>'mATX','Khe RAM'=>'4 khe DDR4','Chuẩn RAM'=>'DDR4']);

addProduct($db, ['cat'=>3,'brand'=>3,'name'=>'ASUS TUF Gaming B550-PLUS','price'=>3290000,'disc'=>0,'qty'=>30,'desc'=>'Mainboard ASUS TUF Gaming B550-PLUS, AM4, DDR4, ATX, WiFi','feat'=>0],
    ['Socket'=>'AM4','Chipset'=>'B550','Kích thước'=>'ATX','Khe RAM'=>'4 khe DDR4','Chuẩn RAM'=>'DDR4']);

echo "\n=== RAM (cat_id=2) ===\n";
addProduct($db, ['cat'=>2,'brand'=>5,'name'=>'Kingston FURY Beast 8GB DDR4 3200MHz','price'=>590000,'disc'=>0,'qty'=>100,'desc'=>'RAM Kingston FURY Beast 8GB DDR4 3200MHz, tản nhiệt nhôm, gaming','feat'=>0],
    ['Dung lượng'=>'8GB','Loại'=>'DDR4','Bus'=>'3200MHz','Điện áp'=>'1.35V','RGB'=>'Không']);

addProduct($db, ['cat'=>2,'brand'=>5,'name'=>'Kingston FURY Beast 32GB DDR4 3200MHz (2x16GB)','price'=>1590000,'disc'=>0,'qty'=>50,'desc'=>'RAM Kingston FURY Beast 32GB DDR4 3200MHz Kit 2x16GB','feat'=>0],
    ['Dung lượng'=>'32GB (2x16GB)','Loại'=>'DDR4','Bus'=>'3200MHz','Điện áp'=>'1.35V','RGB'=>'Không']);

addProduct($db, ['cat'=>2,'brand'=>6,'name'=>'Corsair Vengeance LPX 16GB DDR4 3200MHz','price'=>1090000,'disc'=>5,'qty'=>60,'desc'=>'RAM Corsair Vengeance LPX 16GB DDR4 3200MHz, profile thấp','feat'=>0],
    ['Dung lượng'=>'16GB','Loại'=>'DDR4','Bus'=>'3200MHz','Điện áp'=>'1.35V','RGB'=>'Không']);

addProduct($db, ['cat'=>2,'brand'=>6,'name'=>'Corsair Vengeance RGB Pro 16GB DDR4 3600MHz','price'=>1490000,'disc'=>0,'qty'=>45,'desc'=>'RAM Corsair Vengeance RGB Pro 16GB DDR4 3600MHz, LED RGB 10 zone','feat'=>1],
    ['Dung lượng'=>'16GB','Loại'=>'DDR4','Bus'=>'3600MHz','Điện áp'=>'1.35V','RGB'=>'Có']);

addProduct($db, ['cat'=>2,'brand'=>$gskill,'name'=>'G.Skill Trident Z RGB 32GB DDR4 3600MHz (2x16GB)','price'=>2190000,'disc'=>0,'qty'=>30,'desc'=>'RAM G.Skill Trident Z RGB 32GB DDR4 3600MHz Kit 2x16GB, RGB cao cấp','feat'=>1],
    ['Dung lượng'=>'32GB (2x16GB)','Loại'=>'DDR4','Bus'=>'3600MHz','Điện áp'=>'1.35V','RGB'=>'Có']);

// DDR5
addProduct($db, ['cat'=>2,'brand'=>5,'name'=>'Kingston FURY Beast 32GB DDR5 5200MHz (2x16GB)','price'=>2890000,'disc'=>0,'qty'=>35,'desc'=>'RAM DDR5 Kingston FURY Beast 32GB 5200MHz Kit 2x16GB','feat'=>1],
    ['Dung lượng'=>'32GB (2x16GB)','Loại'=>'DDR5','Bus'=>'5200MHz','Điện áp'=>'1.1V','RGB'=>'Không']);

addProduct($db, ['cat'=>2,'brand'=>6,'name'=>'Corsair Dominator Platinum RGB 32GB DDR5 5600MHz','price'=>4290000,'disc'=>0,'qty'=>20,'desc'=>'RAM Corsair Dominator Platinum RGB 32GB DDR5 5600MHz, hàng top','feat'=>1],
    ['Dung lượng'=>'32GB (2x16GB)','Loại'=>'DDR5','Bus'=>'5600MHz','Điện áp'=>'1.25V','RGB'=>'Có']);

echo "\n=== VGA (cat_id=4) ===\n";
// NVIDIA
addProduct($db, ['cat'=>4,'brand'=>3,'name'=>'ASUS Dual GeForce RTX 3060 12GB','price'=>7990000,'disc'=>10,'qty'=>25,'desc'=>'VGA ASUS Dual RTX 3060 12GB GDDR6, 2 quạt, gaming 1080p/1440p','feat'=>1],
    ['VRAM'=>'12GB GDDR6','Kết nối'=>'PCIe 4.0 x16','Cổng xuất hình'=>'HDMI 2.1, DP 1.4a','TDP'=>'170W']);

addProduct($db, ['cat'=>4,'brand'=>7,'name'=>'MSI Gaming X RTX 3060 Ti 8GB','price'=>9490000,'disc'=>5,'qty'=>18,'desc'=>'VGA MSI Gaming X RTX 3060 Ti 8GB GDDR6, hiệu năng 1440p tốt','feat'=>1],
    ['VRAM'=>'8GB GDDR6','Kết nối'=>'PCIe 4.0 x16','Cổng xuất hình'=>'HDMI 2.1, DP 1.4a x3','TDP'=>'200W']);

addProduct($db, ['cat'=>4,'brand'=>3,'name'=>'ASUS TUF Gaming RTX 3070 OC 8GB','price'=>12990000,'disc'=>0,'qty'=>12,'desc'=>'VGA ASUS TUF Gaming RTX 3070 OC 8GB, 1440p ultra gaming','feat'=>1],
    ['VRAM'=>'8GB GDDR6','Kết nối'=>'PCIe 4.0 x16','Cổng xuất hình'=>'HDMI 2.1, DP 1.4a x3','TDP'=>'240W']);

addProduct($db, ['cat'=>4,'brand'=>8,'name'=>'Gigabyte GeForce RTX 4060 Gaming OC 8GB','price'=>9990000,'disc'=>0,'qty'=>20,'desc'=>'VGA Gigabyte RTX 4060 Gaming OC 8GB GDDR6, Ada Lovelace, DLSS 3','feat'=>1],
    ['VRAM'=>'8GB GDDR6','Kết nối'=>'PCIe 4.0 x16','Cổng xuất hình'=>'HDMI 2.1a, DP 1.4a x3','TDP'=>'115W']);

addProduct($db, ['cat'=>4,'brand'=>7,'name'=>'MSI GeForce RTX 4070 VENTUS 3X 12GB','price'=>16990000,'disc'=>0,'qty'=>10,'desc'=>'VGA MSI RTX 4070 VENTUS 3X 12GB GDDR6X, 1440p/4K gaming','feat'=>1],
    ['VRAM'=>'12GB GDDR6X','Kết nối'=>'PCIe 4.0 x16','Cổng xuất hình'=>'HDMI 2.1, DP 1.4a x3','TDP'=>'200W']);

// AMD Radeon
addProduct($db, ['cat'=>4,'brand'=>$sapphire,'name'=>'Sapphire PULSE RX 6600 8GB','price'=>5490000,'disc'=>8,'qty'=>22,'desc'=>'VGA Sapphire PULSE Radeon RX 6600 8GB GDDR6, 1080p gaming','feat'=>0],
    ['VRAM'=>'8GB GDDR6','Kết nối'=>'PCIe 4.0 x8','Cổng xuất hình'=>'HDMI 2.1, DP 1.4 x3','TDP'=>'132W']);

addProduct($db, ['cat'=>4,'brand'=>$sapphire,'name'=>'Sapphire NITRO+ RX 6700 XT 12GB','price'=>9990000,'disc'=>5,'qty'=>15,'desc'=>'VGA Sapphire NITRO+ RX 6700 XT 12GB GDDR6, 1440p gaming mạnh','feat'=>1],
    ['VRAM'=>'12GB GDDR6','Kết nối'=>'PCIe 4.0 x16','Cổng xuất hình'=>'HDMI 2.1, DP 1.4 x3','TDP'=>'230W']);

addProduct($db, ['cat'=>4,'brand'=>8,'name'=>'Gigabyte Radeon RX 7600 Gaming OC 8GB','price'=>7490000,'disc'=>0,'qty'=>18,'desc'=>'VGA Gigabyte RX 7600 Gaming OC 8GB GDDR6, RDNA 3, 1080p/1440p','feat'=>0],
    ['VRAM'=>'8GB GDDR6','Kết nối'=>'PCIe 4.0 x8','Cổng xuất hình'=>'HDMI 2.1, DP 2.1 x2','TDP'=>'165W']);

echo "\n=== SSD/HDD (cat_id=5) ===\n";
addProduct($db, ['cat'=>5,'brand'=>2,'name'=>'Samsung 870 EVO 500GB SATA SSD','price'=>1290000,'disc'=>0,'qty'=>80,'desc'=>'SSD Samsung 870 EVO 500GB SATA 2.5", tốc độ đọc 560MB/s','feat'=>0],
    ['Dung lượng'=>'500GB','Chuẩn kết nối'=>'SATA III','Form factor'=>'2.5"','Tốc độ đọc'=>'560 MB/s','Tốc độ ghi'=>'530 MB/s']);

addProduct($db, ['cat'=>5,'brand'=>2,'name'=>'Samsung 870 EVO 1TB SATA SSD','price'=>2190000,'disc'=>5,'qty'=>60,'desc'=>'SSD Samsung 870 EVO 1TB SATA 2.5", tốc độ đọc 560MB/s','feat'=>0],
    ['Dung lượng'=>'1TB','Chuẩn kết nối'=>'SATA III','Form factor'=>'2.5"','Tốc độ đọc'=>'560 MB/s','Tốc độ ghi'=>'530 MB/s']);

addProduct($db, ['cat'=>5,'brand'=>2,'name'=>'Samsung 980 PRO 1TB NVMe PCIe 4.0','price'=>2990000,'disc'=>0,'qty'=>40,'desc'=>'SSD Samsung 980 PRO 1TB NVMe PCIe 4.0 M.2, tốc độ 7000MB/s','feat'=>1],
    ['Dung lượng'=>'1TB','Chuẩn kết nối'=>'NVMe PCIe 4.0','Form factor'=>'M.2 2280','Tốc độ đọc'=>'7000 MB/s','Tốc độ ghi'=>'5000 MB/s']);

addProduct($db, ['cat'=>5,'brand'=>9,'name'=>'WD Blue SN570 1TB NVMe PCIe 3.0','price'=>1690000,'disc'=>0,'qty'=>55,'desc'=>'SSD WD Blue SN570 1TB NVMe PCIe 3.0 M.2, tốc độ 3500MB/s','feat'=>0],
    ['Dung lượng'=>'1TB','Chuẩn kết nối'=>'NVMe PCIe 3.0','Form factor'=>'M.2 2280','Tốc độ đọc'=>'3500 MB/s','Tốc độ ghi'=>'3000 MB/s']);

addProduct($db, ['cat'=>5,'brand'=>9,'name'=>'WD Black SN850X 2TB NVMe PCIe 4.0','price'=>5490000,'disc'=>0,'qty'=>25,'desc'=>'SSD WD Black SN850X 2TB NVMe PCIe 4.0 M.2, tốc độ 7300MB/s','feat'=>1],
    ['Dung lượng'=>'2TB','Chuẩn kết nối'=>'NVMe PCIe 4.0','Form factor'=>'M.2 2280','Tốc độ đọc'=>'7300 MB/s','Tốc độ ghi'=>'6600 MB/s']);

addProduct($db, ['cat'=>5,'brand'=>$crucial,'name'=>'Crucial P3 1TB NVMe PCIe 3.0','price'=>1390000,'disc'=>5,'qty'=>70,'desc'=>'SSD Crucial P3 1TB NVMe PCIe 3.0 M.2, giá tốt tốc độ cao','feat'=>0],
    ['Dung lượng'=>'1TB','Chuẩn kết nối'=>'NVMe PCIe 3.0','Form factor'=>'M.2 2280','Tốc độ đọc'=>'3500 MB/s','Tốc độ ghi'=>'3000 MB/s']);

// HDD
addProduct($db, ['cat'=>5,'brand'=>10,'name'=>'Seagate Barracuda 1TB HDD 3.5"','price'=>890000,'disc'=>0,'qty'=>100,'desc'=>'HDD Seagate Barracuda 1TB 7200RPM SATA 3.5", lưu trữ dung lượng lớn','feat'=>0],
    ['Dung lượng'=>'1TB','Chuẩn kết nối'=>'SATA III','Form factor'=>'3.5"','Tốc độ quay'=>'7200 RPM']);

addProduct($db, ['cat'=>5,'brand'=>10,'name'=>'Seagate Barracuda 2TB HDD 3.5"','price'=>1390000,'disc'=>0,'qty'=>80,'desc'=>'HDD Seagate Barracuda 2TB 7200RPM SATA 3.5"','feat'=>0],
    ['Dung lượng'=>'2TB','Chuẩn kết nối'=>'SATA III','Form factor'=>'3.5"','Tốc độ quay'=>'7200 RPM']);

echo "\n=== PSU (cat_id=6) ===\n";
addProduct($db, ['cat'=>6,'brand'=>12,'name'=>'Cooler Master MWE 550W 80+ White','price'=>1190000,'disc'=>0,'qty'=>60,'desc'=>'PSU Cooler Master MWE 550W 80+ White, chứng nhận đồng, bảo hành 5 năm','feat'=>0],
    ['Công suất'=>'550W','Chứng nhận'=>'80+ White','Modular'=>'Không','Bảo hành'=>'5 năm']);

addProduct($db, ['cat'=>6,'brand'=>12,'name'=>'Cooler Master MWE Gold 650W 80+ Gold','price'=>1890000,'disc'=>0,'qty'=>50,'desc'=>'PSU Cooler Master MWE Gold 650W 80+ Gold, hiệu suất 90%','feat'=>0],
    ['Công suất'=>'650W','Chứng nhận'=>'80+ Gold','Modular'=>'Không','Bảo hành'=>'5 năm']);

addProduct($db, ['cat'=>6,'brand'=>11,'name'=>'Seasonic Focus GX-750W 80+ Gold','price'=>2890000,'disc'=>5,'qty'=>30,'desc'=>'PSU Seasonic Focus GX 750W 80+ Gold Full Modular, cao cấp','feat'=>1],
    ['Công suất'=>'750W','Chứng nhận'=>'80+ Gold','Modular'=>'Full Modular','Bảo hành'=>'10 năm']);

addProduct($db, ['cat'=>6,'brand'=>11,'name'=>'Seasonic Prime TX-850W 80+ Titanium','price'=>4990000,'disc'=>0,'qty'=>15,'desc'=>'PSU Seasonic Prime TX 850W 80+ Titanium Full Modular, hiệu suất 94%','feat'=>1],
    ['Công suất'=>'850W','Chứng nhận'=>'80+ Titanium','Modular'=>'Full Modular','Bảo hành'=>'12 năm']);

addProduct($db, ['cat'=>6,'brand'=>$be,'name'=>'be quiet! Straight Power 11 750W 80+ Gold','price'=>2490000,'disc'=>0,'qty'=>25,'desc'=>'PSU be quiet! Straight Power 11 750W 80+ Gold, cực yên tĩnh','feat'=>0],
    ['Công suất'=>'750W','Chứng nhận'=>'80+ Gold','Modular'=>'Semi Modular','Bảo hành'=>'5 năm']);

addProduct($db, ['cat'=>6,'brand'=>$deepcool,'name'=>'DeepCool PQ650M 650W 80+ Gold','price'=>1690000,'disc'=>8,'qty'=>40,'desc'=>'PSU DeepCool PQ650M 650W 80+ Gold Semi Modular, giá tốt','feat'=>0],
    ['Công suất'=>'650W','Chứng nhận'=>'80+ Gold','Modular'=>'Semi Modular','Bảo hành'=>'10 năm']);

echo "\n=== CASE (cat_id=7) ===\n";
addProduct($db, ['cat'=>7,'brand'=>12,'name'=>'Cooler Master MasterBox Q300L','price'=>890000,'disc'=>0,'qty'=>50,'desc'=>'Case Cooler Master MasterBox Q300L, mATX Mini Tower, panel kính cường lực','feat'=>0],
    ['Kích thước'=>'Mini Tower (mATX)','Màu sắc'=>'Đen','Cửa kính'=>'Có','Quạt đi kèm'=>'1x120mm']);

addProduct($db, ['cat'=>7,'brand'=>12,'name'=>'Cooler Master MasterBox 520 Mesh','price'=>1490000,'disc'=>5,'qty'=>35,'desc'=>'Case Cooler Master MasterBox 520 Mesh, ATX Mid Tower, lưới thép, tản nhiệt tốt','feat'=>1],
    ['Kích thước'=>'Mid Tower (ATX)','Màu sắc'=>'Đen','Cửa kính'=>'Có','Quạt đi kèm'=>'3x120mm ARGB']);

addProduct($db, ['cat'=>7,'brand'=>$nzxt,'name'=>'NZXT H510 Flow','price'=>1990000,'disc'=>0,'qty'=>25,'desc'=>'Case NZXT H510 Flow, ATX Mid Tower, mesh mặt trước, thiết kế tối giản','feat'=>1],
    ['Kích thước'=>'Mid Tower (ATX)','Màu sắc'=>'Đen/Trắng','Cửa kính'=>'Có','Quạt đi kèm'=>'2x120mm']);

addProduct($db, ['cat'=>7,'brand'=>$nzxt,'name'=>'NZXT H7 Flow RGB','price'=>3490000,'disc'=>0,'qty'=>15,'desc'=>'Case NZXT H7 Flow RGB, ATX Mid Tower, RGB, airflow xuất sắc','feat'=>1],
    ['Kích thước'=>'Mid Tower (ATX)','Màu sắc'=>'Đen/Trắng','Cửa kính'=>'Có','Quạt đi kèm'=>'3x120mm RGB']);

addProduct($db, ['cat'=>7,'brand'=>$lian,'name'=>'Lian Li PC-O11 Dynamic EVO','price'=>2990000,'disc'=>0,'qty'=>20,'desc'=>'Case Lian Li O11 Dynamic EVO, ATX Mid Tower, kính hai mặt, iconic','feat'=>1],
    ['Kích thước'=>'Mid Tower (ATX/E-ATX)','Màu sắc'=>'Đen/Trắng','Cửa kính'=>'Có (2 mặt)','Quạt đi kèm'=>'0 (mua thêm)']);

addProduct($db, ['cat'=>7,'brand'=>$fractal,'name'=>'Fractal Design Meshify C','price'=>2190000,'disc'=>0,'qty'=>18,'desc'=>'Case Fractal Design Meshify C, ATX Mid Tower, lưới mặt trước, airflow cực tốt','feat'=>0],
    ['Kích thước'=>'Mid Tower (ATX)','Màu sắc'=>'Đen','Cửa kính'=>'Có','Quạt đi kèm'=>'2x120mm']);

addProduct($db, ['cat'=>7,'brand'=>$thermalt,'name'=>'Thermaltake View 71 TG ARGB','price'=>3990000,'disc'=>0,'qty'=>10,'desc'=>'Case Thermaltake View 71 Full Tower, kính 4 mặt, show off build cực đẹp','feat'=>1],
    ['Kích thước'=>'Full Tower (E-ATX)','Màu sắc'=>'Đen','Cửa kính'=>'Có (4 mặt)','Quạt đi kèm'=>'3x120mm ARGB']);

echo "\n============================\n";
echo "✅ Đã thêm: $added sản phẩm\n";
if ($errors) {
    echo "❌ Lỗi (" . count($errors) . "):\n";
    foreach($errors as $e) echo "  - $e\n";
}
