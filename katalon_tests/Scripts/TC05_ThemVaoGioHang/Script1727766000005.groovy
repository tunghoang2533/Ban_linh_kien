import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

/**
 * TC05_ThemVaoGioHang
 * Mục tiêu: Mở trang chi tiết sản phẩm, nhấn Thêm vào giỏ và kiểm tra sản phẩm xuất hiện trong giỏ hàng.
 */

WebUI.comment('=== BẮT ĐẦU TEST CASE 05: THÊM SẢN PHẨM VÀO GIỎ HÀNG ===')

WebUI.openBrowser('')
WebUI.maximizeWindow()

// Điều hướng tới sản phẩm còn hàng (ID 3: AMD Ryzen 5 5600X)
String productUrl = GlobalVariable.G_SiteURL + 'chitietsanpham.php?id=3'
WebUI.navigateToUrl(productUrl)

// Kiểm tra tiêu đề sản phẩm hiển thị
WebUI.waitForElementVisible(findTestObject('Page_ProductDetail/txt_ProductName'), GlobalVariable.G_Timeout)
String productName = WebUI.getText(findTestObject('Page_ProductDetail/txt_ProductName'))
WebUI.comment('Đang xem chi tiết sản phẩm: ' + productName)

// Nhấp nút THÊM VÀO GIỎ
WebUI.waitForElementVisible(findTestObject('Page_ProductDetail/btn_AddToCart'), GlobalVariable.G_Timeout)
WebUI.click(findTestObject('Page_ProductDetail/btn_AddToCart'))

// Chờ chuyển hướng tới trang Giỏ hàng (giohang.php)
WebUI.delay(2)
String cartUrl = WebUI.getUrl()
WebUI.comment('URL giỏ hàng hiện tại: ' + cartUrl)
WebUI.verifyMatch(cartUrl, '.*giohang\\.php.*', true)

// Kiểm tra giỏ hàng có hiển thị tiêu đề và bảng sản phẩm
WebUI.waitForElementVisible(findTestObject('Page_Cart/title_CartHero'), GlobalVariable.G_Timeout)
WebUI.waitForElementVisible(findTestObject('Page_Cart/table_CartTable'), GlobalVariable.G_Timeout)

// Xác minh tên sản phẩm vừa thêm có trong bảng giỏ hàng
WebUI.verifyTextPresent('Ryzen', false)

WebUI.comment('=== THÊM SẢN PHẨM VÀO GIỎ HÀNG THÀNH CÔNG ===')

WebUI.closeBrowser()
