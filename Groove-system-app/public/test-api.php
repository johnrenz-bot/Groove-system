<?php
// test-api.php - Place this in your public folder
$apiKey = 'AIzaSyArh0K6bOOe_CxCJWKpqVPnSMDn5FSSUGg';
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}";

$data = [
    'contents' => [
        'parts' => [
            ['text' => "Hello, how are you?"]
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL verification for testing
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
}
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h2>API Test Results</h2>";
echo "HTTP Code: $httpCode<br>";
echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";

// Try to decode JSON
$jsonResponse = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "JSON decoded successfully.<br>";
    if (isset($jsonResponse['candidates'][0]['content']['parts'][0]['text'])) {
        echo "Generated text: " . htmlspecialchars($jsonResponse['candidates'][0]['content']['parts'][0]['text']);
    }
} else {
    echo "JSON decode error: " . json_last_error_msg() . "<br>";
    echo "Raw response might be an error message.";
}