<?php
// ==========================================
// WEB APPLICATION FIREWALL (WAF) - PHP NATIVE
// ==========================================

function inspect_payload($payload) {
    if (empty($payload)) return null;

    // 1. DAFTAR ATURAN (RULES ENGINE)
    $waf_rules = [
        "XSS (Cross-Site Scripting)" => "/(<script.*?>|javascript:|onerror=|onload=)/i",
        "SQL Injection"              => "/(union\s+select|select\s+.*\s+from|' OR '1'='1|--\s*$)/i",
        "Path Traversal"             => "/(\.\.\/\.\.\/|\/etc\/passwd|\/windows\/win\.ini)/i",
        "Command Injection"          => "/(;.*(bash|sh|cmd|powershell|wget|curl|ping|whoami))/i"
    ];

    // 2. ANTI-EVASION (Mencegah penyamaran karakter seperti %27 menjadi ')
    $decoded_payload = urldecode($payload);

    // 3. INSPEKSI PAYLOAD
    foreach ($waf_rules as $attack_name => $pattern) {
        if (preg_match($pattern, $decoded_payload) || preg_match($pattern, $payload)) {
            return $attack_name;
        }
    }
    return null;
}

function run_waf() {
    $attack_detected = null;
    $payload_source = '';

    // Cek semua parameter URL (GET)
    foreach ($_GET as $key => $value) {
        $attack_detected = inspect_payload($value);
        if ($attack_detected) {
            $payload_source = "URL Parameter ($key)";
            break;
        }
    }

    // Cek semua input Form (POST)
    if (!$attack_detected) {
        foreach ($_POST as $key => $value) {
            $attack_detected = inspect_payload($value);
            if ($attack_detected) {
                $payload_source = "Form POST Data ($key)";
                break;
            }
        }
    }

    // 4. SISTEM BLOKIR & LOGGING
    if ($attack_detected) {
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $time = date('Y-m-d H:i:s');
        $request_uri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        
        // Simpan jejak serangan ke file Log (Untuk Report)
        $log_message = "[$time] [!!!] ALERT: $attack_detected dari IP $client_ip | Target: $request_uri | Sumber: $payload_source\n";
        file_put_contents(__DIR__ . '/waf_alerts.log', $log_message, FILE_APPEND);

        // Tampilkan halaman Error 403 ke Hacker
        header('HTTP/1.0 403 Forbidden');
        echo "<div style='font-family: Arial, sans-serif; text-align: center; margin-top: 100px;'>";
        echo "<h1 style='color: #d9534f; font-size: 50px;'>⛔ 403 Forbidden</h1>";
        echo "<h2>Akses Ditolak oleh Web Application Firewall (WAF)</h2>";
        echo "<p>Aktivitas mencurigakan terdeteksi. IP Anda ($client_ip) telah dicatat ke dalam log.</p>";
        echo "</div>";
        die(); // Hentikan eksekusi web PHP seketika
    }
}

// Menyalakan WAF
run_waf();
?>
