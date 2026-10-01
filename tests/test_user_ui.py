"""Regression tests for responsive customer account UI."""
from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class UserUiRegressionTests(unittest.TestCase):
    def test_logged_in_account_page_does_not_bind_missing_form(self):
        view = (ROOT / "app/views/user/taikhoan_view.php").read_text(encoding="utf-8")
        self.assertIn("if (registerForm)", view)
        self.assertNotIn("document.getElementById('form-register').addEventListener", view)

    def test_mobile_header_allows_search_to_shrink(self):
        header = (ROOT / "app/views/header.php").read_text(encoding="utf-8")
        self.assertIn(".search-bar { min-width: 0; }", header)
        self.assertIn(".search-bar input { min-width: 0;", header)
        self.assertIn(".user-menu .user-menu-item { display: none; }", header)

    def test_user_forms_have_mobile_layouts(self):
        for relative in (
            "app/views/user/taikhoan_view.php",
            "app/views/user/thongtin_view.php",
            "app/views/user/doimatkhau_view.php",
        ):
            with self.subTest(view=relative):
                content = (ROOT / relative).read_text(encoding="utf-8")
                self.assertIn("@media (max-width: 600px)", content)
        profile = (ROOT / "app/views/user/thongtin_view.php").read_text(encoding="utf-8")
        self.assertIn(".profile-form .field-row { grid-template-columns: 1fr; }", profile)


if __name__ == "__main__":
    unittest.main()
