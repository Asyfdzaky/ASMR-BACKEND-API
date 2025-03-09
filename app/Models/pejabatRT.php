<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pejabatRT extends Model
{
    use HasFactory;
    protected $table = 'pejabat_rt';
    protected $fillable = ['id_rt', 'nama_pejabat_rt'];

    public function rt()
    {
        return $this->belongsTo(RT::class, 'id_rt');
    }

    public function approvalSurat()
    {
        return $this->hasMany(ApprovalSurat::class, 'id_pejabat_rt');
    }
}
