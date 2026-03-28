<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../include/db.php';// lấy mã api đã dc định nghĩa
// Lấy dữ liệu JSON từ yêu cầu POST
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = $data['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Vui lòng nhập câu hỏi.']);
    exit;
}

// 1. DÁN CHÌA KHÓA BẮT ĐẦU BẰNG AIza CỦA BẠN VÀO ĐÂY:
$apiKey = API_KEY; 

// 2. Link gọi API sử dụng gemini-1.5-flash chuẩn nhất
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
// Cấu hình hệ thống và cách trả lời
$systemPrompt = "Bạn là nhân viên CSKH của web Truyện Hay. Hãy trả lời ngắn gọn, súc tích và nhiệt tình. Câu hỏi: ";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $systemPrompt . $userMessage]
            ]
        ]
    ]
];
// gọi API bằng cURL bảo mật
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);

if ($response === false) {
    $reply = "Lỗi mạng nội bộ (cURL Error): " . curl_error($ch);
} else {
    $responseData = json_decode($response, true);
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = $responseData['candidates'][0]['content']['parts'][0]['text'];
        $reply = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $reply); 
        $reply = str_replace("\n", "<br>", $reply); 
    } else {
        if (isset($responseData['error'])) {
            $reply = 'Lỗi từ Google: ' . $responseData['error']['message'];
        } else {
            $reply = 'Không có kết quả. Phản hồi thô: ' . htmlspecialchars($response);
        }
    }
}
curl_close($ch);
//câu trả lời ai gửi lại footer
echo json_encode(['reply' => $reply]);
exit;
?>