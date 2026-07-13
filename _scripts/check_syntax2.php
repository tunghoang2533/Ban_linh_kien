<?php
$code = file_get_contents(__DIR__ . '/../admin/views/dashboard/index.php');
$lines = explode("\n", $code);

$colonIfCount = 0;
$endifCount = 0;
$colonIfLines = [];

foreach ($lines as $i => $line) {
    $n = $i + 1;
    // Count colon-syntax if blocks: <?php if (...):
    if (preg_match('/<\?php\s+if\s*\(/i', $line) && preg_match('/:\s*$/', trim($line))) {
        $colonIfCount++;
        $colonIfLines[] = "Line $n: " . trim($line);
    }
    // Also count if (...): without closing tag on same line
    if (preg_match('/<\?php\s+if\s*\(/', $line) && !preg_match('/\?>/', $line) && preg_match('/:\s*$/', trim($line))) {
        // Already counted above
    }
    // Count endif
    if (preg_match('/<\?php\s+endif/i', $line)) {
        $endifCount++;
    }
    // Count endforeach
    if (preg_match('/<\?php\s+endforeach/i', $line)) {
        echo "Line $n: ENDFOREACH\n";
    }
    // Check for unclosed <?php tags
    if (preg_match('/<\?php/', $line) && !preg_match('/\?>\s*$/', $line) && !preg_match('/:\s*$/', $line)) {
        // PHP opening tag without closing on same line - might span multiple lines
    }
}

echo "\nColon-syntax if blocks: $colonIfCount\n";
echo "Endif blocks: $endifCount\n\n";
echo "Colon-syntax if lines:\n";
foreach ($colonIfLines as $l) {
    echo "  $l\n";
}

// Check for unclosed <?php tags (excluding short echo tags)
$phpOpen = 0;
foreach (token_get_all($code) as $t) {
    if (!is_array($t)) continue;
    if ($t[0] === T_OPEN_TAG) $phpOpen++;
    if ($t[0] === T_CLOSE_TAG) $phpOpen--;
}
echo "\nUnclosed PHP tags (should be 0): $phpOpen\n";
