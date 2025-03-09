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
        Schema::create('approval_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')->constrained('pengajuan_surats')->onDelete('cascade');
            $table->foreignId('id_pejabat_rt')->constrained('pejabat_rt')->onDelete('cascade');
            $table->foreignId('id_pejabat_rw')->constrained('pejabat_rw')->onDelete('cascade');
            $table->enum('status_approval', ['Pending', 'Disetujui_RT', 'Ditolak_RT', 'Disetujui_RW', 'Ditolak_RW', 'Selesai']);
            $table->text('catatan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_surats');
    }
};
