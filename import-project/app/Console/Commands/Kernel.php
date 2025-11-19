<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Uygulamanın zamanlanmış komutlarını tanımlayın.
     */
    protected function schedule(Schedule $schedule): void
    {
        // VİRGÜLÜ UNUTMAYIN!

        // 👇 Sizin import komutunuz burada tanımlanacak.
        // Her gün, sabah 03:00'te 'import:products' komutunu çalıştır.
        $schedule->command('import:products')
                 ->dailyAt('03:00') // Çalışma sıklığı ve saati
                 ->withoutOverlapping() // Önceki işlem bitmeden yenisinin başlamasını engeller
                 ->onSuccess(function () {
                     // Başarılı olursa (Opsiyonel: log yazabilirsiniz)
                     // Log::info('Ürün içe aktarımı başarılı.');
                 })
                 ->onFailure(function () {
                     // Hata olursa (Opsiyonel: log yazabilir veya bildirim gönderebilirsiniz)
                     // Log::error('Ürün içe aktarımında hata oluştu.');
                 });

        // Diğer zamanlanmış görevler buraya eklenir...
    }

    /**
     * Uygulamanın komutlarını yükleyin.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}