import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

/**
 * TC04_TimKiemSanPham
 * Mục tiêu: Tìm kiếm sản phẩm theo từ khóa từ thanh tìm kiếm ở Header và xác nhận kết quả trả về.
 */

WebUI.comment('=== BẮT ĐẦU TEST CASE 04: TÌM KIẾM SẢN PHẨM ===')

WebUI.openBrowser('')
WebUI.maximizeWindow()

WebUI.navigateToUrl(GlobalVariable.G_SiteURL + 'index.php')

String keyword = 'Ryzen'

// Nhập từ khóa vào ô tìm kiếm trên Header
WebUI.waitForElementVisible(findTestObject('Page_Home/input_SearchKeyword'), GlobalVariable.G_Timeout)
WebUI.setText(findTestObject('Page_Home/input_SearchKeyword'), keyword)

// Nhấn nút Tìm kiếm
WebUI.click(findTestObject('Page_Home/btn_SearchSubmit'))

// Xác nhận URL chuyển đến search.php
WebUI.delay(1)
String currentUrl = WebUI.getUrl()
WebUI.verifyMatch(currentUrl, '.*search\\.php\\?key=' + keyword + '.*', true)

// Xác nhận hiển thị danh sách sản phẩm khớp với từ khóa
WebUI.waitForElementVisible(findTestObject('Page_Home/item_ProductCardName'), GlobalVariable.G_Timeout)
String productName = WebUI.getText(findTestObject('Page_Home/item_ProductCardName'))
WebUI.comment('Sản phẩm tìm thấy đầu tiên: ' + productName)
WebUI.verifyTextPresent(keyword, false)

WebUI.comment('=== TÌM KIẾM SẢN PHẨM THÀNH CÔNG ===')

WebUI.closeBrowser()
