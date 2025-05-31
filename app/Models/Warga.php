<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warga extends Model
{
    use HasFactory;
    protected $table = 'wargas';
    protected $fillable = [
        'id_users',
        'id_alamat',
        'id_rt',
        'id_rw',
        'nama',
        'nomor_kk',
        'agama',
        'nik',
        'jenis_kelamin',
        'phone',
        'tempat_lahir',
        'tanggal_lahir',
    ];

    /**
     * Relasi ke User (Warga terkait dengan satu User)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    /**
     * Relasi ke Alamat
     */
    public function alamat()
    {
        return $this->belongsTo(DetailAlamat::class, 'id_alamat');
    }

    /**
     * Relasi ke RT
     */
    public function rt()
    {
        return $this->belongsTo(RT::class, 'id_rt');
    }


    /**
     * Relasi ke Pejabat RT (jika warga adalah pejabat RT)
     */
    public function pejabatRT()
    {
        return $this->hasOne(pejabatRT::class, 'id_warga');
    }

    /**
     * Relasi ke Pejabat RW (jika warga adalah pejabat RW)
     */
    public function pejabatRW()
    {
        return $this->hasOne(pejabatRW::class, 'id_warga');
    }

    /**
     * Relasi ke Pengajuan Surat
     */
    public function pengajuanSurat()
    {
        return $this->hasMany(PengajuanSurat::class, 'id_warga');
    }
}
