<?php
$code = file_get_contents(__DIR__ . '/../admin/views/dashboard/index.php');
$tokens = @token_get_all($code);
if ($tokens === false) {
    echo "Failed to tokenize\n";
    exit(1);
}
$depth = 0;
$stack = [];
$lineMap = [];

foreach ($tokens as $i => $t) {
    if (!is_array($t)) continue;
    
    if ($t[0] === T_IF) {
        $depth++;
        $stack[$depth] = ['type' => 'if', 'line' => $t[2]];
        $lineMap[] = ['line' => $t[2], 'event' => 'IF OPEN', 'depth' => $depth];
    } elseif ($t[0] === T_ELSEIF) {
        $lineMap[] = ['line' => $t[2], 'event' => 'ELSEIF', 'depth' => $depth];
    } elseif ($t[0] === T_ELSE) {
        $lineMap[] = ['line' => $t[2], 'event' => 'ELSE', 'depth' => $depth];
    } elseif ($t[0] === T_ENDIF) {
        $closes = isset($stack[$depth]) ? $stack[$depth]['line'] : '?';
        $lineMap[] = ['line' => $t[2], 'event' => "ENDIF (closes line $closes)", 'depth' => $depth];
        unset($stack[$depth]);
        $depth--;
    }
}

echo "Final depth: $depth\n\n";
if ($depth > 0) {
    echo "Unclosed blocks:\n";
    foreach ($stack as $d => $info) {
        echo "  Depth $d: {$info['type']} at line {$info['line']}\n";
    }
} else {
    echo "All blocks properly closed!\n";
}

echo "\nFull trace:\n";
foreach ($lineMap as $lm) {
    echo sprintf("Line %4d: %-35s (depth %d)\n", $lm['line'], $lm['event'], $lm['depth']);
}
