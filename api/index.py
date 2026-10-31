from http.server import BaseHTTPRequestHandler
import json
from urllib.parse import parse_qs, urlparse

class handler(BaseHTTPRequestHandler):
    def do_GET(self):
        # Parse parameter dari URL
        parsed_path = urlparse(self.path)
        query_params = parse_qs(parsed_path.query)
        
        # Ambil User-Agent dari query ?ua=... atau dari header
        user_agent = query_params.get('ua', [self.headers.get('User-Agent', '')])[0]

        # Logika deteksi sederhana atau balasan JSON perangkat
        brand = "Unknown"
        model = "Device"
        
        ua_lower = user_agent.lower()
        if "samsung" in ua_lower or "sm-" in ua_lower:
            brand = "Samsung"
            if "s24" in ua_lower: model = "Galaxy S24 Ultra"
        elif "xiaomi" in ua_lower or "redmi" in ua_lower or "poco" in ua_lower:
            brand = "Xiaomi"
            if "note 14" in ua_lower: model = "Redmi Note 14 4G"
        elif "iphone" in ua_lower:
            brand = "Apple"
            model = "iPhone"

        # URL Gambar Badge / Logo Inisial Profesional yang anti 404
        image_url = f"https://ui-avatars.com/api/?name={brand}&size=512&background=0D1117&color=00E5FF&bold=true&format=png&rounded=true"

        response_data = {
            "error": False,
            "brand": brand,
            "model": model,
            "image_url": image_url,
            "userAgent": user_agent
        }

        # Kirim header respons JSON
        self.send_response(200)
        self.send_header('Content-type', 'application/json; charset=utf-8')
        self.send_header('Access-Control-Allow-Origin', '*')
        self.end_headers()
        
        self.wfile.write(json.dumps(response_data, indent=4).encode('utf-8'))
        return