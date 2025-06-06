<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalSurat extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_pengajuan',
        'id_rt',
        'id_rw',
        'status_approval',
        'catatan',
        'approved_at',
    ];

    public function pengajuanSurat()
    {
        return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan');
    }

    public function RT()
    {
        return $this->belongsTo(RT::class, 'id_rt');
    }

    public function RW()
    {
        return $this->belongsTo(RW::class, 'id_rw');
    }
}
