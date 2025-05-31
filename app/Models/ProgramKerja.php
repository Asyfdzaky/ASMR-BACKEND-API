<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramKerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_program_kerja',
        'tanggal_mulai',
        'tanggal_selesai',
        'tempat',
        'waktu_mulai',
        'waktu_selesai',
        'penanggung_jawab',
        'id_rw',
    ];

    public function rw()
    {
        return $this->belongsTo(RW::class, 'id_rw');
    }
}
