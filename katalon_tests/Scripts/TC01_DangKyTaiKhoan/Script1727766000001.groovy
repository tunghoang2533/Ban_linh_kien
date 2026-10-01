import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

/**
 * TC01_DangKyTaiKhoan
 * Mục tiêu: Xác minh người dùng có thể đăng ký tài khoản thành công với thông tin hợp lệ.
 */

WebUI.comment('=== BẮT ĐẦU TEST CASE 01: ĐĂNG KÝ TÀI KHOẢN MỚI ===')

// 1. Mở trình duyệt và phóng to cửa sổ
WebUI.openBrowser('')
WebUI.maximizeWindow()

// 2. Điều hướng đến trang tài khoản (Đăng nhập / Đăng ký)
String targetUrl = GlobalVariable.G_SiteURL + 'taikhoan.php'
WebUI.navigateToUrl(targetUrl)

// 3. Nhấp chọn tab 'Đăng ký'
WebUI.waitForElementVisible(findTestObject('Page_Auth/btn_TabRegister'), GlobalVariable.G_Timeout)
WebUI.click(findTestObject('Page_Auth/btn_TabRegister'))

// 4. Sinh dữ liệu ngẫu nhiên để không bị trùng lặp tài khoản
long rnd = System.currentTimeMillis() % 1000000
String uniqueUser = 'user_' + rnd
String uniqueEmail = 'test_' + rnd + '@gmail.com'

// 5. Điền thông tin vào form Đăng ký
WebUI.setText(findTestObject('Page_Auth/input_RegFullname'), 'Nguyen Van ' + rnd)
WebUI.setText(findTestObject('Page_Auth/input_RegEmail'), uniqueEmail)
WebUI.setText(findTestObject('Page_Auth/input_RegUsername'), uniqueUser)
WebUI.setText(findTestObject('Page_Auth/input_RegPassword'), 'MatKhau123!')
WebUI.setText(findTestObject('Page_Auth/input_RegConfirmPassword'), 'MatKhau123!')

// 6. Nhấn nút ĐĂNG KÝ NGAY
WebUI.click(findTestObject('Page_Auth/btn_RegSubmit'))

// 7. Chờ và kiểm tra thông báo đăng ký thành công
WebUI.waitForElementVisible(findTestObject('Page_Auth/msg_RegSuccess'), GlobalVariable.G_Timeout)
WebUI.verifyElementText(findTestObject('Page_Auth/msg_RegSuccess'), 'Đăng ký thành công! Hãy đăng nhập.')

WebUI.comment('=== ĐĂNG KÝ THÀNH CÔNG VỚI TÀI KHOẢN: ' + uniqueUser + ' ===')

// 8. Đóng trình duyệt
WebUI.closeBrowser()
