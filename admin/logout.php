<?php
session_start();
session_destroy();

// Redirect tới trang admin (sẽ tự nhảy tới login nếu không có session)
header('Location: index.php');
?>
