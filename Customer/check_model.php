<?php
// DÁN CHÌA KHÓA CỦA BẠN VÀO ĐÂY:
$apiKey = 'AIzaSyAE93I6sOt_CUUEIwjams75GV7COtmHzt8'; 

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

echo "<h3>Danh sách các model AI mà tài khoản của bạn hỗ trợ:</h3>";
echo "<pre>";
print_r(json_decode($response, true));
echo "</pre>";
?>