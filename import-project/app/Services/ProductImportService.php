<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class ProductImportService
{
    protected string $baseUrl;

    public function __construct()
    {
        // BURAYI DEĞİŞTİR: Gerçek API URL'ini yaz
        $this->baseUrl = config('services.products_api.base_url', 'https://fakestoreapi.com/products');
        // örnek: 'https://www.trendyol.com/sr/api/products'
        // örnek: 'https://api.n11.com/v2/products'
    }

    public function fetchPageWithRetries(int $page, int $attempts = 3): ?array
    {
        $delay = 2; // başa biraz daha uzun bekleme koy (rate limit için)

        for ($try = 1; $try <= $attempts; $try++) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept'     => 'application/json',
                        // Gerekirse buraya token vs. ekle:
                        // 'Authorization' => 'Bearer xxx',
                    ])
                    ->get($this->baseUrl, [
                        'page'  => $page,
                        'limit' => 100, // veya per_page, size vs.
                    ]);

                // 429 Rate Limit → biraz daha uzun bekle
                if ($response->status() === 429) {
                    Log::warning("Rate limit yakalandı, 15 saniye bekleniyor...");
                    sleep(15);
                    continue;
                }

                if (!$response->successful()) {
                    Log::warning("API hatası", [
                        'status' => $response->status(),
                        'body'   => $response->body()
                    ]);
                    continue;
                }

                $json = $response->json();

                // EN ÖNEMLİ KISIM: Her türlü JSON yapısını kabul et
                $products = $json['data'] 
                         ?? $json['products'] 
                         ?? $json['items'] 
                         ?? $json['result'] 
                         ?? $json['list']
                         ?? $json['results']
                         ?? ($json['response']['data'] ?? null)
                         ?? ($json['payload'] ?? null)
                         ?? $json; // direkt array dönebiliyor (örneğin fakestoreapi)

                if (!is_array($products)) {
                    Log::error("Ürün listesi bulunamadı", ['response' => $json]);
                    return null;
                }

                // Boş sayfa kontrolü
                if (empty($products)) {
                    return []; // boş array döndür, komut dursun
                }

                // HER ŞEY TAMAM → array dönsün
                return $products; // Artık komut ['data'] beklemiyor, direkt array alıyor

            } catch (\Throwable $e) {
                Log::warning("API bağlantı hatası, deneme {$try}/{$attempts}", [
                    'error' => $e->getMessage()
                ]);
            }

            sleep($delay);
            $delay *= 2; // 2, 4, 8 saniye...
        }

        Log::error("API'den sayfa çekilemedi", ['page' => $page]);
        return null;
    }

    public function validateProduct(array $data): array
    {
        $errors = [];

        $id = $data['id'] ?? $data['product_id'] ?? null;
        if (!$id) $errors[] = 'id eksik';

        $title = $data['title'] ?? $data['name'] ?? $data['product_name'] ?? null;
        if (!$title) $errors[] = 'title/name eksik';

        $price = $data['price'] ?? $data['sale_price'] ?? null;
        if ($price === null || !is_numeric($price)) $errors[] = 'price geçersiz';

        return [
            'ok' => empty($errors),
            'errors' => $errors
        ];
    }

    public function storeProduct(array $data): Product
    {
        $apiId = $data['id'] ?? $data['product_id'] ?? null;

        return Product::updateOrCreate(
            ['api_id' => $apiId],
            [
                'title'       => $data['title'] ?? $data['name'] ?? 'İsim yok',
                'price'       => (float) ($data['price'] ?? $data['sale_price'] ?? 0),
                'description' => $data['description'] ?? $data['desc'] ?? null,
                'image'       => $data['image'] ?? $data['images'][0] ?? $data['thumbnail'] ?? null,
                'stock'       => (int) ($data['stock'] ?? $data['quantity'] ?? 0),
                'status'      => $data['status'] ?? 'active',
            ]
        );
    }
}