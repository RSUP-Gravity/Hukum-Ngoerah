# 📊 Panduan Load Testing - Sistem Manajemen Dokumen Hukum

> **Target:** 3000+ concurrent users  
> **Tool:** k6 (https://k6.io)

---

## 📋 Daftar Isi

1. [Instalasi k6](#instalasi-k6)
2. [Konfigurasi Test](#konfigurasi-test)
3. [Menjalankan Test](#menjalankan-test)
4. [Interpretasi Hasil](#interpretasi-hasil)
5. [Optimisasi](#optimisasi)

---

## 🔧 Instalasi k6

### macOS
```bash
brew install k6
```

### Windows
```bash
choco install k6
```

### Linux (Debian/Ubuntu)
```bash
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```

### Docker
```bash
docker pull grafana/k6
```

---

## 📁 File Test

File test tersedia di folder `tests/load/`:

| File | Deskripsi |
|------|-----------|
| `load-test.js` | Test dasar dengan 100-500 VUs |
| `stress-test.js` | Stress test dengan 3000+ VUs |
| `spike-test.js` | Spike test untuk traffic mendadak |
| `soak-test.js` | Endurance test untuk durasi panjang |

---

## 🚀 Menjalankan Test

### 1. Load Test Dasar (100-500 users)
```bash
cd /path/to/project
k6 run tests/load/load-test.js
```

### 2. Stress Test (3000+ users)
```bash
k6 run tests/load/stress-test.js
```

### 3. Custom Configuration
```bash
# Override VUs dan duration
k6 run --vus 1000 --duration 5m tests/load/load-test.js

# Dengan environment variables
k6 run -e BASE_URL=https://production.example.com tests/load/load-test.js
```

### 4. Export Results ke JSON
```bash
k6 run --out json=results.json tests/load/load-test.js
```

### 5. Export ke InfluxDB (untuk Grafana)
```bash
k6 run --out influxdb=http://localhost:8086/k6 tests/load/load-test.js
```

---

## 📈 Target Metrics

| Metric | Target | Description |
|--------|--------|-------------|
| **http_req_duration (p95)** | < 2000ms | 95% requests harus selesai dalam 2 detik |
| **http_req_duration (p99)** | < 5000ms | 99% requests harus selesai dalam 5 detik |
| **http_req_failed** | < 1% | Kurang dari 1% request gagal |
| **http_reqs** | > 100/s | Minimal 100 requests per second |
| **vus_max** | 3000 | Mampu handle 3000 concurrent users |

---

## 📊 Interpretasi Hasil

### Contoh Output
```
          /\      |‾‾| /‾‾/   /‾‾/   
     /\  /  \     |  |/  /   /  /    
    /  \/    \    |     (   /   ‾‾\  
   /          \   |  |\  \ |  (‾)  | 
  / __________ \  |__| \__\ \_____/ .io

  execution: local
     script: tests/load/load-test.js
     output: -

  scenarios: (100.00%) 1 scenario, 500 max VUs, 6m30s max duration
           default: Up to 500 looping VUs for 6m0s

     ✓ status is 200
     ✓ response time < 2000ms
     ✓ login successful

     checks.........................: 99.85% ✓ 29955  ✗ 45
     data_received..................: 125 MB 347 kB/s
     data_sent......................: 12 MB  33 kB/s
     http_req_blocked...............: avg=1.2ms    min=0s       med=0s     max=1.2s    p(90)=0s     p(95)=0s
     http_req_duration..............: avg=245.3ms  min=12.3ms   med=189ms  max=4.5s    p(90)=512ms  p(95)=823ms
     http_req_failed................: 0.15%  ✓ 45     ✗ 29955
     http_req_receiving.............: avg=1.2ms    min=0s       med=0s     max=312ms   p(90)=1ms    p(95)=2ms
     http_req_sending...............: avg=0.1ms    min=0s       med=0s     max=45ms    p(90)=0s     p(95)=0s
     http_req_waiting...............: avg=244ms    min=12ms     med=188ms  max=4.5s    p(90)=510ms  p(95)=820ms
     http_reqs......................: 30000  83.33/s
     vus............................: 500    min=1    max=500
     vus_max........................: 500    min=500  max=500
```

### Analisis Metrics

1. **http_req_duration (p95) = 823ms** ✅
   - Target < 2000ms → PASS
   
2. **http_req_failed = 0.15%** ✅
   - Target < 1% → PASS

3. **http_reqs = 83.33/s** ✅
   - Target > 100/s → Mendekati target

---

## 🔧 Optimisasi Jika Gagal

### 1. Response Time Tinggi
```php
// Tambahkan caching di controller
public function index()
{
    return Cache::remember('documents.index', 300, function () {
        return Document::with(['type', 'unit'])->paginate(20);
    });
}
```

### 2. Database Bottleneck
```bash
# Periksa slow queries
php artisan db:monitor

# Tambahkan index
php artisan make:migration add_index_to_documents_table
```

### 3. Memory Issues
```ini
# Tambahkan di php.ini
memory_limit = 512M
max_execution_time = 60
```

### 4. Connection Pool
```php
// config/database.php
'mysql' => [
    'pool' => [
        'min_connections' => 10,
        'max_connections' => 100,
    ],
],
```

---

## 📝 Checklist Sebelum Load Test

- [ ] Backup database production
- [ ] Pastikan server tidak digunakan untuk production
- [ ] Monitor server resources (CPU, RAM, Disk I/O)
- [ ] Siapkan rollback plan
- [ ] Informasikan tim terkait

---

## 🎯 Test Scenarios

### Scenario 1: Normal Load
- **VUs:** 100-500
- **Duration:** 5 minutes
- **Ramp-up:** 1 minute

### Scenario 2: Peak Load
- **VUs:** 1000-2000
- **Duration:** 10 minutes
- **Ramp-up:** 2 minutes

### Scenario 3: Stress Test
- **VUs:** 3000+
- **Duration:** 15 minutes
- **Ramp-up:** 5 minutes

### Scenario 4: Spike Test
- **VUs:** 100 → 3000 → 100
- **Duration:** 10 minutes
- **Pattern:** Sudden spike

---

*Dokumentasi ini adalah bagian dari Sistem Manajemen Dokumen Hukum RS Ngoerah*
