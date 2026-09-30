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

    // 4. Database mapping gambar perangkat (bisa diperluas atau ditarik dari database)
    $deviceImageDatabase = [
        "Redmi Note 14 4G" => "https://www.whatmydevice.com/images/devices/xiaomi-redmi-note-14-4g.png",
        "Redmi Note 12 Pro 4G" => "https://www.whatmydevice.com/images/devices/xiaomi-redmi-note-12-pro-4g.png"
    ];

    // Cari gambar berdasarkan model yang terdeteksi secara persis atau parsial
    $deviceImageUrl = null;
    foreach ($deviceImageDatabase as $modelKey => $imageUrl) {
        if (!empty($detectedModel) && stripos($detectedModel, $modelKey) !== false) {
            $deviceImageUrl = $imageUrl;
            break;
        }
    }

    // Fallback jika model spesifik tidak ada di database mapping, tapi mengandung string "Redmi Note 14"
    if (!$deviceImageUrl && (stripos($userAgent, "Redmi Note 14") !== false || (!empty($detectedModel) && stripos($detectedModel, "Redmi Note 14") !== false))) {
        $deviceImageUrl = "https://www.whatmydevice.com/images/devices/xiaomi-redmi-note-14-4g.png"; // Atur default gambar seri ini
    }

    // Fallback umum jika gambar sama sekali tidak ditemukan
    if (!$deviceImageUrl) {
        $deviceImageUrl = "https://via.placeholder.com/150?text=" . urlencode($detectedModel ?: "Device");
    }

    // 5. Susun struktur data JSON (disertakan tambahan 'image_url')
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
        "image_url" => $deviceImageUrl, // <-- Penambahan otomatis URL gambar perangkat
        "icons" => [
            "browser" => null,
            "os" => "/icons/os/" . ($dd->getOs()['short_name'] ?? '') . ".png",
            "device" => "/icons/devices/" . $dd->getDeviceName() . ".png",
            "brand" => "/icons/brand/" . (is_array($brandName) ? ($brandName['name'] ?? '') : $brandName) . ".png"
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