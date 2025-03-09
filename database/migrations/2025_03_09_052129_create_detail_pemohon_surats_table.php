<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_pemohon_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_warga')->constrained('wargas')->onDelete('cascade');
            $table->string('nama_pemohon');
            $table->string('nik_pemohon');
            $table->string('no_kk_pemohon');
            $table->string('alamat_pemohon');
            $table->string('phone_pemohon');
            $table->string('tempat_tanggal_lahir_pemohon');
            $table->enum('jenis_kelamin_pemohon', ['Pria', 'Perempuan']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pemohon_surats');
    }
};
