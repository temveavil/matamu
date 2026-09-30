<?php
// Aktifkan pelaporan error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1. Cek folder vendor
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo json_encode([
        "error" => true,
        "message" => "Folder 'vendor' tidak ditemukan! Jalankan 'composer require matomo/device-detector' terlebih dahulu."
    ], JSON_PRETTY_PRINT);
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use DeviceDetector\DeviceDetector;

// 2. Ambil parameter User-Agent dari URL (?ua=...) atau dari header server
$userAgent = isset($_GET['ua']) ? trim($_GET['ua']) : '';

if (empty($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
}

if (empty($userAgent)) {
    echo json_encode([
        "error" => true,
        "message" => "Parameter 'ua' tidak ditemukan. Contoh: http://localhost:8000/?ua=Mozilla/5.0..."
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    // 3. Inisialisasi dan Parse User-Agent
    $dd = new DeviceDetector($userAgent);
    $dd->parse();

    $detectedModel = $dd->getModel();
    $brandName = $dd->getBrandName();
    
    // Ambil string brand murni
    $brandStr = is_array($brandName) ? ($brandName['name'] ?? '') : $brandName;

    // 4. Generator URL Gambar dengan struktur folder model saja, tapi file gambar gabungan brand-model
    $deviceImageUrl = null;
    if (!empty($detectedModel) && !empty($brandStr)) {
        $cleanBrand = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $brandStr)));
        
        // Slug khusus untuk folder (hanya model)
        $slugModelOnly = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-\s]/', '', $detectedModel)));
        $slugModelOnly = preg_replace('/\s+/', '-', $slugModelOnly);

        // Slug khusus untuk nama file (brand + model)
        $slugBrandAndModel = strtolower(trim(preg_replace('/[^a-zA-Z0-9\-\s]/', '', $brandStr . " " . $detectedModel)));
        $slugBrandAndModel = preg_replace('/\s+/', '-', $slugBrandAndModel);

        // Susun URL: /brand/[model-saja]/[brand-model].jpg
        $deviceImageUrl = "https://whatmydevice.com/data/devices/" . $cleanBrand . "/" . $slugModelOnly . "/" . $slugBrandAndModel . ".jpg";
    }

    // Fallback terakhir jika model tidak terdeteksi
    if (!$deviceImageUrl) {
        $deviceImageUrl = "https://whatmydevice.com/data/devices/samsung/galaxy-s24-ultra/samsung-galaxy-s24-ultra.jpg";
    }

    // 5. Susun struktur data JSON
    $result = [
        "isBot" => $dd->isBot(),
        "clientInfo" => $dd->getClient(),
        "browserFamily" => null,
        "isMobileOnlyBrowser" => false,
        "osInfo" => $dd->getOs(),
        "osFamily" => $dd->getOs()['family'] ?? null,
        "device" => $dd->getDevice(),
        "deviceName" => $dd->getDeviceName(),
        "deviceBrand" => $brandName,
        "model" => $detectedModel,
        "image_url" => $deviceImageUrl,
        "icons" => [
            "browser" => null,
            "os" => "/icons/os/" . ($dd->getOs()['short_name'] ?? '') . ".png",
            "device" => "/icons/devices/" . $dd->getDeviceName() . ".png",
            "brand" => "/icons/brand/" . $brandStr . ".png"
        ],
        "clientHints" => [
            "architecture" => "",
            "app" => "",
            "formFactors" => (object)[],
            "bitness" => "",
            "mobile" => false,
            "model" => "",
            "platform" => "",
            "platformVersion" => "",
            "uaFullVersion" => "",
            "fullVersionList" => (object)[]
        ],
        "headers" => null,
        "userAgent" => $userAgent
    ];

    // 6. Cetak output JSON
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
