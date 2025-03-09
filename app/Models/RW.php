<?php

namespace App\Models;

use App\Models\RT;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RW extends Model
{
    use HasFactory;
    protected $table = 'RW';
    protected $fillable = ['nama_rw'];

    public function rt()
    {
        return $this->hasMany(RT::class, 'id_rw');
    }

    public function pejabatRW()
    {
        return $this->hasMany(pejabatRW::class, 'id_rw');
    }
}
