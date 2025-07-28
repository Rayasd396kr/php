<?php

// 開發環境顯示錯誤，生產環境請移除或設為 Off
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- 1. 設定 API 金鑰 (請替換為你的實際金鑰！在生產環境請使用環境變數) ---
$api_key = "AIzaSyAzKiOmCnqUIFz0u5Fp_uZD9HGOXGEipTM";

// --- 2. 設定模型與獲取查詢內容 ---
// 直接指定要使用的模型名稱，因為你已確認此模型支援文本生成
$model = "gemini-2.5-flash"; 
$geminiQuery = $_GET['q'] ?? ''; // 使用 $_GET 獲取 URL 參數 'q'

// --- 3. 基本輸入驗證 ---
if (empty($geminiQuery)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Query parameter (q) is missing.']);
    exit();
}

// --- 4. 建構 Gemini API 請求 ---
// API 的 URL 格式需要 "models/" 前綴
$fullModelName = "models/" . $model;
$url = "https://generativelanguage.googleapis.com/v1beta/{$fullModelName}:generateContent?key={$api_key}";

$data = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                [
                    "text" => $geminiQuery
                ]
            ]
        ]
    ]
];

$jsonPayload = json_encode($data);

// --- 5. 執行 cURL 請求 ---
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// --- 6. 錯誤處理與響應 ---
if (curl_errno($ch)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit();
}

curl_close($ch);

header('Content-Type: application/json');

if ($http_status >= 400) {
    http_response_code($http_status);
}

echo $response; // 直接輸出 Gemini API 的 JSON 響應