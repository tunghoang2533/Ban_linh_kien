import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

/**
 * TC02_DangNhapThanhCong
 * Mục tiêu: Tạo tài khoản mới rồi đăng nhập thành công vào hệ thống.
 */

WebUI.comment('=== BẮT ĐẦU TEST CASE 02: ĐĂNG NHẬP THÀNH CÔNG ===')

WebUI.openBrowser('')
WebUI.maximizeWindow()

String accountUrl = GlobalVariable.G_SiteURL + 'taikhoan.php'
WebUI.navigateToUrl(accountUrl)

// Chuẩn bị tài khoản mới để kịch bản chạy độc lập và ổn định
long rnd = System.currentTimeMillis() % 1000000
String username = 'logintest_' + rnd
String email = 'login_' + rnd + '@gmail.com'
String password = 'Password@123'

// Bước 1: Đăng ký nhanh tài khoản
WebUI.waitForElementVisible(findTestObject('Page_Auth/btn_TabRegister'), GlobalVariable.G_Timeout)
WebUI.click(findTestObject('Page_Auth/btn_TabRegister'))

WebUI.setText(findTestObject('Page_Auth/input_RegFullname'), 'Tester Dang Nhap')
WebUI.setText(findTestObject('Page_Auth/input_RegEmail'), email)
WebUI.setText(findTestObject('Page_Auth/input_RegUsername'), username)
WebUI.setText(findTestObject('Page_Auth/input_RegPassword'), password)
WebUI.setText(findTestObject('Page_Auth/input_RegConfirmPassword'), password)
WebUI.click(findTestObject('Page_Auth/btn_RegSubmit'))

WebUI.waitForElementVisible(findTestObject('Page_Auth/msg_RegSuccess'), GlobalVariable.G_Timeout)

// Bước 2: Chuyển sang form Đăng nhập
WebUI.click(findTestObject('Page_Auth/btn_TabLogin'))
WebUI.setText(findTestObject('Page_Auth/input_LoginUsername'), username)
WebUI.setText(findTestObject('Page_Auth/input_LoginPassword'), password)
WebUI.click(findTestObject('Page_Auth/btn_LoginSubmit'))

// Bước 3: Xác minh đăng nhập thành công
// Khi đăng nhập thành công, hệ thống chuyển hướng về index.php
WebUI.delay(2)
String currentUrl = WebUI.getUrl()
WebUI.comment('URL sau đăng nhập: ' + currentUrl)

// Quay lại trang tài khoản để verify bảng người dùng đã đăng nhập
WebUI.navigateToUrl(accountUrl)
WebUI.waitForElementVisible(findTestObject('Page_Auth/panel_LoggedIn'), GlobalVariable.G_Timeout)
WebUI.verifyTextPresent('Xin chào', false)

WebUI.comment('=== ĐĂNG NHẬP THÀNH CÔNG VỚI TÀI KHOẢN: ' + username + ' ===')

WebUI.closeBrowser()
