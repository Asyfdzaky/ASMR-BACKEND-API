<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateSurat extends Model
{
    use HasFactory;
    protected $table = 'template_surat';
    protected $fillable = ['jenis_surat', 'template_html'];

    public function approvalSurat()
    {
        return $this->hasMany(ApprovalSurat::class, 'id_template_surat');
    }
}
