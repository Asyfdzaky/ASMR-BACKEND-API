<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class pejabatRW extends Model
{
    use HasFactory;
    protected $table = 'pejabat_rw';
    protected $fillable = ['id_rw', 'nama_pejabat_rw'];

    public function rw()
    {
        return $this->belongsTo(RW::class, 'id_rw');
    }
}
