<?php
// database/migrations/xxxx_add_api_id_to_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 'api_id' sütununu 'id' sütunundan sonra ekliyoruz.
            // Benzersiz olmalı ve indeksi olmalı.
            $table->unsignedBigInteger('api_id')->after('id')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Geri alma (rollback) durumunda sütunu sileriz.
            $table->dropColumn('api_id');
        });
    }
};