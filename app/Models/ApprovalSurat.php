<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalSurat extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_pengajuan',
        'id_pejabat_rt',
        'id_pejabat_rw',
        'status_approval',
        'catatan',
        'approved_at',
    ];

    public function pengajuanSurat()
    {
        return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan');
    }

    public function pejabatRT()
    {
        return $this->belongsTo(pejabatRT::class, 'id_pejabat_rt');
    }

    public function pejabatRW()
    {
        return $this->belongsTo(pejabatRW::class, 'id_pejabat_rw');
    }
}
