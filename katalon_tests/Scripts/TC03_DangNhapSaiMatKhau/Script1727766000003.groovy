import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

/**
 * TC03_DangNhapSaiMatKhau
 * Mục tiêu: Kiểm tra hệ thống hiển thị thông báo lỗi khi người dùng nhập sai mật khẩu.
 */

WebUI.comment('=== BẮT ĐẦU TEST CASE 03: ĐĂNG NHẬP SAI MẬT KHẨU ===')

WebUI.openBrowser('')
WebUI.maximizeWindow()

WebUI.navigateToUrl(GlobalVariable.G_SiteURL + 'taikhoan.php')

// Đảm bảo đang ở tab Đăng nhập
WebUI.waitForElementVisible(findTestObject('Page_Auth/btn_TabLogin'), GlobalVariable.G_Timeout)
WebUI.click(findTestObject('Page_Auth/btn_TabLogin'))

// Nhập tài khoản và mật khẩu sai
WebUI.setText(findTestObject('Page_Auth/input_LoginUsername'), 'admin')
WebUI.setText(findTestObject('Page_Auth/input_LoginPassword'), 'MatKhauSaiChacChan_999')

// Nhấn ĐĂNG NHẬP
WebUI.click(findTestObject('Page_Auth/btn_LoginSubmit'))

// Chờ và xác minh thông báo lỗi hiển thị
WebUI.waitForElementVisible(findTestObject('Page_Auth/msg_LoginError'), GlobalVariable.G_Timeout)
WebUI.verifyTextPresent('Sai tài khoản hoặc mật khẩu', false)

WebUI.comment('=== XÁC MINH THÀNH CÔNG: HỆ THỐNG ĐÃ BÁO LỖI KHI NHẬP SAI MẬT KHẨU ===')

WebUI.closeBrowser()
