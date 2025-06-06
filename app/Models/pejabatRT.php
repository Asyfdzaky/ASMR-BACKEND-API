<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class pejabatRT extends Model
{
    use HasFactory;
    protected $table = 'pejabat_rt';
    protected $fillable = ['id_rt', 'id_warga','nama_pejabat_rt','periode_mulai','periode_selesai','ttd'];

    public function rt()
    {
        return $this->belongsTo(RT::class, 'id_rt');
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga');
    }

    public function approvalSurat()
    {
        return $this->hasMany(ApprovalSurat::class, 'id_pejabat_rt');
    }
}
