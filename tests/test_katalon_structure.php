<?php
/**
 * Test Suite kiểm tra tính toàn vẹn của dự án Katalon Studio
 * Chạy test: php tests/test_katalon_structure.php
 */

$passed = 0;
$failed = 0;

function it($title, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $title\n";
        $passed++;
    } else {
        echo "  [FAIL] $title\n";
        $failed++;
    }
}

echo "\n=======================================================\n";
echo "   KIỂM TRA CẤU TRÚC DỰ ÁN KATALON STUDIO (katalon_tests)\n";
echo "=======================================================\n\n";

$baseDir = __DIR__ . '/../katalon_tests';

// 1. Kiểm tra Project descriptor
echo "Test 1: Kiểm tra cấu hình dự án (.prj & .project)...\n";
it("Tồn tại file BanLinhKien_KatalonTest.prj", file_exists("$baseDir/BanLinhKien_KatalonTest.prj"));
$prjXml = @simplexml_load_file("$baseDir/BanLinhKien_KatalonTest.prj");
it("File .prj có định dạng XML hợp lệ", $prjXml !== false && (string)$prjXml->name === 'BanLinhKien_KatalonTest');
it("Tồn tại file .project", file_exists("$baseDir/.project"));

// 2. Kiểm tra Profiles
echo "\nTest 2: Kiểm tra Profiles và GlobalVariables...\n";
it("Tồn tại file Profiles/default.glbl", file_exists("$baseDir/Profiles/default.glbl"));
$glblXml = @simplexml_load_file("$baseDir/Profiles/default.glbl");
it("File default.glbl là XML hợp lệ", $glblXml !== false);
$hasSiteUrl = false;
if ($glblXml) {
    foreach ($glblXml->GlobalVariableEntity as $entity) {
        if ((string)$entity->name === 'G_SiteURL') {
            $hasSiteUrl = true;
            break;
        }
    }
}
it("Profile chứa biến G_SiteURL", $hasSiteUrl);

// 3. Kiểm tra Test Cases
echo "\nTest 3: Kiểm tra các Test Cases (.tc và .groovy)...\n";
$expectedTCs = [
    'TC01_DangKyTaiKhoan'     => 'Script1727766000001.groovy',
    'TC02_DangNhapThanhCong'   => 'Script1727766000002.groovy',
    'TC03_DangNhapSaiMatKhau'  => 'Script1727766000003.groovy',
    'TC04_TimKiemSanPham'      => 'Script1727766000004.groovy',
    'TC05_ThemVaoGioHang'      => 'Script1727766000005.groovy',
    'TC06_GuiLienHe'           => 'Script1727766000006.groovy',
];

foreach ($expectedTCs as $tcName => $scriptName) {
    $tcFile = "$baseDir/Test Cases/$tcName.tc";
    $scriptFile = "$baseDir/Scripts/$tcName/$scriptName";

    it("Test Case $tcName.tc tồn tại và hợp lệ", file_exists($tcFile) && @simplexml_load_file($tcFile) !== false);
    it("Script Groovy $scriptName tồn tại", file_exists($scriptFile));
    if (file_exists($scriptFile)) {
        $content = file_get_contents($scriptFile);
        it("Script $tcName chứa lệnh WebUI", strpos($content, 'WebUI.') !== false);
    }
}

// 4. Kiểm tra Object Repository
echo "\nTest 4: Kiểm tra Object Repository (.rs)...\n";
$expectedObjects = [
    'Page_Auth/btn_TabLogin.rs',
    'Page_Auth/btn_TabRegister.rs',
    'Page_Auth/input_LoginUsername.rs',
    'Page_Auth/input_LoginPassword.rs',
    'Page_Auth/btn_LoginSubmit.rs',
    'Page_Auth/msg_LoginError.rs',
    'Page_Auth/panel_LoggedIn.rs',
    'Page_Auth/input_RegFullname.rs',
    'Page_Auth/input_RegEmail.rs',
    'Page_Auth/input_RegUsername.rs',
    'Page_Auth/input_RegPassword.rs',
    'Page_Auth/input_RegConfirmPassword.rs',
    'Page_Auth/btn_RegSubmit.rs',
    'Page_Auth/msg_RegSuccess.rs',
    'Page_Home/input_SearchKeyword.rs',
    'Page_Home/btn_SearchSubmit.rs',
    'Page_Home/item_ProductCardName.rs',
    'Page_ProductDetail/txt_ProductName.rs',
    'Page_ProductDetail/btn_AddToCart.rs',
    'Page_Cart/title_CartHero.rs',
    'Page_Cart/table_CartTable.rs',
    'Page_Contact/input_ContactName.rs',
    'Page_Contact/input_ContactEmail.rs',
    'Page_Contact/input_ContactPhone.rs',
    'Page_Contact/select_ContactSubject.rs',
    'Page_Contact/textarea_ContactMessage.rs',
    'Page_Contact/btn_ContactSubmit.rs',
    'Page_Contact/msg_ContactSuccess.rs',
];

$allObjectsValid = true;
foreach ($expectedObjects as $objPath) {
    $fullPath = "$baseDir/Object Repository/$objPath";
    if (!file_exists($fullPath) || @simplexml_load_file($fullPath) === false) {
        $allObjectsValid = false;
        echo "  [FAIL] Đối tượng thiếu hoặc lỗi XML: $objPath\n";
    }
}
it("Toàn bộ 28 Test Objects tồn tại và có định dạng XML chuẩn", $allObjectsValid);

// 5. Kiểm tra Test Suite
echo "\nTest 5: Kiểm tra Test Suite (TS_Regression_BanLinhKien.ts)...\n";
$tsFile = "$baseDir/Test Suites/TS_Regression_BanLinhKien.ts";
it("File Test Suite TS_Regression_BanLinhKien.ts tồn tại", file_exists($tsFile));
$tsXml = @simplexml_load_file($tsFile);
it("Test Suite có định dạng XML hợp lệ", $tsXml !== false);
$linkedCount = 0;
if ($tsXml) {
    $linkedCount = count($tsXml->testCaseLink);
}
it("Test Suite liên kết đủ 6 Test Cases", $linkedCount === 6);

// 6. Kiểm tra tài liệu hướng dẫn
echo "\nTest 6: Kiểm tra tài liệu README_HUONG_DAN_KATALON.md...\n";
it("Tồn tại tài liệu README_HUONG_DAN_KATALON.md", file_exists("$baseDir/README_HUONG_DAN_KATALON.md"));

echo "\n-------------------------------------------------------\n";
echo "Kết quả kiểm thử Katalon Structure: $passed passed, $failed failed.\n";
echo "=======================================================\n";

exit($failed > 0 ? 1 : 0);
