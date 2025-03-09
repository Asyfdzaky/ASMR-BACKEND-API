<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailPemohonSurat extends Model
{
    use HasFactory;
    protected $table = 'detail_pemohon_surats';
    protected $fillable = [
        'id_warga',
        'nama_pemohon',
        'nik_pemohon',
        'no_kk_pemohon',
        'alamat_pemohon',
        'phone_pemohon',
        'tempat_tanggal_lahir_pemohon',
        'jenis_kelamin_pemohon',
    ];

    /**
     * Relasi ke model Warga (Setiap detail pemohon terkait dengan seorang warga)
     */
    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga');
    }

    /**
     * Relasi ke PengajuanSurat (Setiap detail pemohon bisa punya banyak pengajuan)
     */
    public function pengajuanSurat()
    {
        return $this->hasMany(PengajuanSurat::class, 'id_detailPemohon');
    }
}
