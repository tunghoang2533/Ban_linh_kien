<?php
/**
 * Chatbot Build PC - Groq AI (LLaMA 3.3 70B)
 */
ob_start();
require_once 'session_check.php';
require_once 'config.php'; // config.php kiểm soát error_reporting và đọc .env

header('Content-Type: application/json; charset=utf-8');

require_once 'core/Database.php';

$db = Database::getInstance();

// ── Cấu hình Groq ──────────────────────────────────────────────
define('GROQ_API_KEY', getenv('GROQ_API_KEY') ?: '');
define('GROQ_URL',    'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MODEL',  'llama-3.3-70b-versatile');

// ── Danh mục ───────────────────────────────────────────────────
$catMap = [
    1 => 'Vi xử lý (CPU)',
    3 => 'Bo mạch chủ (Mainboard)',
    2 => 'Bộ nhớ trong (RAM)',
    4 => 'Card màn hình (VGA)',
    5 => 'Ổ cứng (SSD/HDD)',
    6 => 'Nguồn máy tính (PSU)',
    7 => 'Vỏ máy tính (Case)',
];

// ── Nhận request ───────────────────────────────────────────────
$input   = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$history = $input['history'] ?? [];

if ($message === '') {
    ob_clean();
    echo json_encode(['reply' => 'Xin hãy nhập câu hỏi của bạn.', 'suggestions' => []]);
    exit;
}

// ── Lấy sản phẩm từ DB ─────────────────────────────────────────
function getProductContext($db, $catMap) {
    $sql = "SELECT p.id, p.name, p.price, p.quantity, p.category_id,
                   p.discount_percent,
                   ps.spec_value as socket
            FROM products p
            LEFT JOIN product_specs ps ON p.id = ps.product_id AND ps.spec_name = 'Socket'
            WHERE p.is_active = 1 AND p.quantity > 0
            ORDER BY p.category_id, p.price ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];
    foreach ($rows as $r) {
        $cname  = $catMap[$r['category_id']] ?? 'Khác';
        $price  = number_format($r['price'], 0, ',', '.');
        $line   = "  [ID:{$r['id']}] {$r['name']} | {$price}đ";
        if (!empty($r['socket']))          $line .= " | Socket:{$r['socket']}";
        if ($r['discount_percent'] > 0)    $line .= " | GIẢM {$r['discount_percent']}%";
        $grouped[$cname][] = $line;
    }

    $text = '';
    foreach ($grouped as $cat => $items) {
        $text .= "\n[$cat]\n" . implode("\n", $items) . "\n";
    }
    return $text;
}

// ── System Prompt ──────────────────────────────────────────────
function buildSystemPrompt($productList) {
    return <<<PROMPT
Bạn là trợ lý tư vấn Build PC chuyên nghiệp của cửa hàng linh kiện máy tính tại Việt Nam.

## NHIỆM VỤ:
- Tư vấn cấu hình PC theo ngân sách và nhu cầu khách hàng
- Chỉ gợi ý sản phẩm có trong danh sách kho hàng bên dưới (dùng đúng ID)
- Ưu tiên sản phẩm đang GIẢM GIÁ khi phù hợp
- Đảm bảo CPU và Mainboard phải cùng Socket
- Trả lời TIẾNG VIỆT, thân thiện, ngắn gọn, chuyên nghiệp

## QUY TẮC ĐỊNH DẠNG:
1. Dùng **bold** cho tên sản phẩm và giá
2. Mỗi linh kiện gợi ý viết trên 1 dòng, bắt đầu bằng ký hiệu ↳
3. Khi gợi ý cấu hình đầy đủ, PHẢI thêm dòng cuối:
   BUILD_JSON:[{"cat_id":1,"product_id":ID},{"cat_id":2,"product_id":ID},...]
   (cat_id: 1=CPU, 2=RAM, 3=Mainboard, 4=VGA, 5=SSD, 6=PSU, 7=Case)
4. Cuối mỗi trả lời PHẢI thêm:
   SUGGESTIONS:["gợi ý 1","gợi ý 2","gợi ý 3"]

## PHÂN BỔ NGÂN SÁCH:
- Gaming:    CPU 20%, MB 12%, RAM 8%, VGA 35%, SSD 10%, PSU 8%, Case 7%
- Đồ họa:   CPU 25%, MB 12%, RAM 12%, VGA 25%, SSD 12%, PSU 8%, Case 6%
- Lập trình: CPU 28%, MB 13%, RAM 15%, VGA 15%, SSD 15%, PSU 8%, Case 6%
- Văn phòng: CPU 25%, MB 15%, RAM 12%, VGA 15%, SSD 13%, PSU 10%, Case 10%

## KHO HÀNG:
$productList

## LƯU Ý:
- Không bịa sản phẩm ngoài danh sách
- Nếu ngân sách quá thấp, báo thật và gợi ý ngân sách tối thiểu
- Liệt kê tối đa 5 sản phẩm khi trả lời câu hỏi đơn lẻ
PROMPT;
}

// ── Gọi Groq API ───────────────────────────────────────────────
function callGroq($systemPrompt, $history, $message) {
    $messages = [['role' => 'system', 'content' => $systemPrompt]];

    foreach ($history as $h) {
        if (!empty($h['role']) && !empty($h['content'])) {
            $role = $h['role'] === 'assistant' ? 'assistant' : 'user';
            $messages[] = ['role' => $role, 'content' => $h['content']];
        }
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    $payload = json_encode([
        'model'       => GROQ_MODEL,
        'messages'    => $messages,
        'temperature' => 0.6,
        'max_tokens'  => 1200,
    ]);

    $ch = curl_init(GROQ_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) return null;

    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? null;
}

// ── Parse phản hồi AI ──────────────────────────────────────────
function parseReply($raw) {
    $buildSuggestion = [];
    $suggestions     = [];
    $reply           = $raw;

    // BUILD_JSON
    if (preg_match('/BUILD_JSON:\s*(\[[\s\S]*?\])/m', $raw, $m)) {
        $json = json_decode($m[1], true);
        if (is_array($json)) $buildSuggestion = $json;
        $reply = str_replace($m[0], '', $reply);
    }

    // SUGGESTIONS
    if (preg_match('/SUGGESTIONS:\s*(\[[\s\S]*?\])/m', $raw, $m)) {
        $json = json_decode($m[1], true);
        if (is_array($json)) $suggestions = array_values(array_slice($json, 0, 4));
        $reply = str_replace($m[0], '', $reply);
    }

    if (!empty($buildSuggestion) && !in_array('Áp dụng cấu hình này', $suggestions)) {
        array_unshift($suggestions, 'Áp dụng cấu hình này');
    }

    if (empty($suggestions)) {
        $suggestions = ['Gợi ý cấu hình gaming', 'Xem CPU có sẵn', 'Xem VGA có sẵn', 'Tư vấn thêm'];
    }

    return [
        'reply'            => trim($reply),
        'suggestions'      => $suggestions,
        'build_suggestion' => $buildSuggestion,
    ];
}

// ── Fallback ───────────────────────────────────────────────────
function fallbackReply($message) {
    $msg = mb_strtolower($message, 'UTF-8');
    foreach (['xin chào','chào','hello','hi'] as $w) {
        if (str_contains($msg, $w)) {
            return [
                'reply'       => "👋 **Xin chào!** Tôi là trợ lý Build PC.\n\nHãy cho tôi biết ngân sách và nhu cầu, tôi sẽ gợi ý cấu hình phù hợp nhất!",
                'suggestions' => ['Build PC gaming 15 triệu', 'Build PC văn phòng 10 triệu', 'Build PC đồ họa 20 triệu'],
            ];
        }
    }
    return [
        'reply'       => "⚠️ Đang gặp sự cố kết nối AI. Vui lòng thử lại sau!\n\nBạn có thể hỏi tôi về:\n• **Gợi ý cấu hình** theo ngân sách\n• **Tư vấn linh kiện** CPU, VGA, RAM...",
        'suggestions' => ['Build PC gaming 15 triệu', 'Build PC văn phòng 10 triệu', 'Xem CPU có sẵn'],
    ];
}

// ── Main ───────────────────────────────────────────────────────
try {
    $productList  = getProductContext($db, $catMap);
    $systemPrompt = buildSystemPrompt($productList);
    $rawReply     = callGroq($systemPrompt, $history, $message);

    $result = $rawReply ? parseReply($rawReply) : fallbackReply($message);
} catch (Exception $e) {
    $result = fallbackReply($message);
}

ob_clean();
echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
