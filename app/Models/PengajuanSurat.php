<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanSurat extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_warga',
        'id_detailPemohon',
        'id_rt',
        'id_rw',
        'jenis_surat',
        'keterangan',
        'file_surat',
        'status',
        'created_at',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga');
    }

    public function rt()
    {
        return $this->belongsTo(RT::class, 'id_rt');
    }

    public function rw()
    {
        return $this->belongsTo(RW::class, 'id_rw');
    }

    public function approvalSurat()
    {
        return $this->hasOne(ApprovalSurat::class, 'id_pengajuan');
    }
}