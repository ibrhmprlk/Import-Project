<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // Validator'ı ekledik
use App\Models\Product;
use App\Models\ProductError; // ProductError modelini ekledik (varsayarak)

class ImportProducts extends Command
{
    protected $signature = 'import:products {--page=1} {--limit=3}';
    protected $description = 'API\'den ürünleri çeker - %100 ÇALIŞIR';

    // Global sayıcıları tanımlıyoruz
    protected $successfulImports = 0;
    protected $failedValidations = 0;
    protected $dbErrors = 0;

    public function handle()
    {
        $page = (int) $this->option('page');
        $limit = (int) $this->option('limit');

        $this->info("Başlıyor → Sayfa: $page, Limit: $limit");

        // BURAYI DEĞİŞTİR → Gerçek API URL'in buraya gelsin
        $baseUrl = 'https://fakestoreapi.com/products';  // TEST İÇİN ÇALIŞIR
        // $baseUrl = 'https://api.ornekmagaza.com/v2/products';  // kendi API'n

        $processedPages = 0;

        // while döngüsünü $limit kadar sayfayı işlemeye ayarladık
        while ($processedPages < $limit) {
            $currentPage = $page + $processedPages;

            // Rate limit (10 istek/dakika) gereksinimi için 6 saniye bekleme.
            if ($processedPages > 0) {
                $this->info("\nRate limit bekleme süresi (6 saniye)...");
                sleep(6); 
            }
            
            $this->info("\nÇekiliyor → page=$currentPage");

            if (!$this->importPage($baseUrl, $currentPage)) {
                $this->warn("Sayfa boş geldi veya API hata verdi, duruyor.");
                break;
            }

            $processedPages++;
        }

        $this->info("\nBİTTİ! Toplam {$processedPages} sayfa çekildi.");
        $this->line("-----------------------------------------");
        $this->info("✅ Başarıyla Kaydedilen Ürün: {$this->successfulImports}");
        $this->warn("❌ Doğrulama Hatası (Hata Kaydı Yapıldı): {$this->failedValidations}");
        if ($this->dbErrors > 0) {
            $this->error("🚨 Veritabanı Kayıt Hatası: {$this->dbErrors}");
        }
    }

  private function importPage($baseUrl, $page)
{
    // Fakestore API'si page değil limit ve offset kullanır. 
    $offset = ($page - 1) * 100;
    $url = $baseUrl . '?limit=100&offset=' . $offset;

    $response = null;

    // Ağ hatası veya rate limit durumunda 3 defa deneme (Retry Mekanizması)
    for ($i = 1; $i <= 3; $i++) { 
        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'Laravel Import Command'])
                ->get($url);

            if ($response->successful()) break;

            if ($response->status() === 429) {
                $this->warn("Rate limit! 15 saniye bekleniyor...");
                sleep(15);
                continue;
            }
        } catch (\Exception $e) {
            $this->warn("Bağlantı hatası, tekrar deneniyor... ($i/3)");
            sleep(3);
        }
    }

    if (!$response || !$response->successful()) {
        $status = $response ? $response->status() : 'bağlantı yok';
        $this->error("Sayfa çekilemedi: Hata kodu {$status}");
        Log::error("API Çekim Hatası", ['url' => $url, 'status' => $status]);
        return true;
    }

    $data = $response->json();
    if (empty($data)) return false;

    $items = is_array($data) ? $data : ($data['data'] ?? $data['products'] ?? $data['items'] ?? []);
    if (empty($items)) return false;

    $this->info(count($items) . " ürün bulundu.");

    $bar = $this->output->createProgressBar(count($items));
    $bar->start();

    // 👇 KRİTİK EKSİK KISIM: TRANSACTION VE ROLLBACK EKLEME
    DB::beginTransaction();
    $pageSuccess = true; // Sayfa işleminin başarılı olup olmadığını tutar

    try {
        foreach ($items as $item) {
            $bar->advance();
            $apiId = $item['id'] ?? null;
            
            // 1. Doğrulama
            $validator = Validator::make($item, [
                'id'    => 'required', 
                'title' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                // 2. Geçersiz Ürün Kaydı
                $this->failedValidations++;
                ProductError::create([
                    'api_id' => $apiId,
                    'raw_data' => json_encode($item),
                    'errors' => json_encode($validator->errors()->all()),
                ]);
                continue; // Hatalıysa kaydetme adımını atla
            }

            // 3. Geçerli Ürünü Veritabanına Kaydetme
            Product::updateOrCreate(
                ['api_id' => $apiId],
                [
                    'title'       => $item['title'] ?? $item['name'] ?? 'İsim yok',
                    'price'       => (float)($item['price'] ?? 0),
                    'description' => $item['description'] ?? null,
                    'image'       => $item['image'] ?? ($item['images'][0] ?? null),
                ]
            );
            $this->successfulImports++;
        }

        // Eğer döngü başarıyla biterse:
        DB::commit();

    } catch (\Exception $e) {
        // Eğer döngü sırasında Veritabanı (veya beklenmedik başka bir) hatası oluşursa:
        DB::rollBack(); // 👈 Tüm ürünleri geri al!
        $this->dbErrors += count($items); // Hata sayısını sayfa boyutu kadar artırıyoruz
        $pageSuccess = false;
        Log::error('Sayfa Transaction Hatası (ROLLBACK YAPILDI)', ['sayfa' => $page, 'hata' => $e->getMessage()]);
    }

    $bar->finish();
    $this->newLine();
    return $pageSuccess; // Rollback olursa, handle metodunda döngüden çıkmak için false döner
}
}