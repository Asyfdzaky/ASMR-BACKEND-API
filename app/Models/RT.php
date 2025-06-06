<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RT extends Model
{
    use HasFactory;
    protected $table = 'rt';
    protected $fillable = ['id_rw', 'nama_rt'];

    public function warga()
    {
        return $this->hasMany(Warga::class, 'id_rt');
    }

    public function rw()
    {
        return $this->belongsTo(RW::class, 'id_rw');
    }

    public function pejabatRT()
    {
        return $this->hasMany(pejabatRT::class, 'id_rt');
    }
}
