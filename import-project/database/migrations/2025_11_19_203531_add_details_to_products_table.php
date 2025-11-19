<?php
// database/migrations/xxxx_add_details_to_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 'description' sütunu zaten var, bu yüzden kaldırıldı.
            // Sadece eksik olan 'image' sütununu ekliyoruz:
            $table->string('image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Geri alma (rollback) durumunda sadece eklediğimiz 'image' sütununu sileriz.
            $table->dropColumn(['image']); 
            // 'description' zaten tabloda vardı, onu silmeye çalışmamalıyız.
        });
    }
};
