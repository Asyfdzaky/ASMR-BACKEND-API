<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_pengajuan_surat')->nullable();
            $table->unsignedBigInteger('id_program_kerja')->nullable();
            $table->text('pesan');
            $table->enum('jenis_notif',['surat','proker','lainnya']);
            $table->timestamps();

            $table->foreign('id_pengajuan_surat')->references('id')->on('pengajuan_surats')->onDelete('cascade');
            $table->foreign('id_program_kerja')->references('id')->on('program_kerjas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifikasi');
    }
};