import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

/**
 * TC06_GuiLienHe
 * Mục tiêu: Điền thông tin vào form liên hệ tại trang lienhe.php và gửi thành công.
 */

WebUI.comment('=== BẮT ĐẦU TEST CASE 06: GỬI LIÊN HỆ TRỰC TUYẾN ===')

WebUI.openBrowser('')
WebUI.maximizeWindow()

WebUI.navigateToUrl(GlobalVariable.G_SiteURL + 'lienhe.php')

// Kiểm tra tiêu đề trang liên hệ
WebUI.verifyTextPresent('Liên Hệ Với Chúng Tôi', false)

// Điền thông tin form liên hệ
WebUI.waitForElementVisible(findTestObject('Page_Contact/input_ContactName'), GlobalVariable.G_Timeout)
WebUI.setText(findTestObject('Page_Contact/input_ContactName'), 'Nguyễn Văn Kiểm Thử')
WebUI.setText(findTestObject('Page_Contact/input_ContactEmail'), 'tester_katalon@gmail.com')
WebUI.setText(findTestObject('Page_Contact/input_ContactPhone'), '0988776655')
WebUI.selectOptionByValue(findTestObject('Page_Contact/select_ContactSubject'), 'Tư vấn cấu hình PC', false)
WebUI.setText(findTestObject('Page_Contact/textarea_ContactMessage'), 'Tôi cần tư vấn cấu hình máy tính gaming hiệu năng cao trong tầm giá 25 triệu đồng.')

// Nhấn nút Gửi tin nhắn
WebUI.click(findTestObject('Page_Contact/btn_ContactSubmit'))

// Xác minh thông báo thành công
WebUI.waitForElementVisible(findTestObject('Page_Contact/msg_ContactSuccess'), GlobalVariable.G_Timeout)
WebUI.verifyTextPresent('Cảm ơn bạn! Tin nhắn của bạn đã được gửi thành công', false)

WebUI.comment('=== GỬI LIÊN HỆ THÀNH CÔNG ===')

WebUI.closeBrowser()
