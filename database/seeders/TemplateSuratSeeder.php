<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSuratSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'jenis_surat' => 'Pengantar KTP',
                'template_html' => file_get_contents(resource_path('views/templates/surat-pengantar-ktp.html')),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_surat' => 'Keterangan Domisili',
                'template_html' => file_get_contents(resource_path('views/templates/surat-keterangan-domisili.html')),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_surat' => 'Surat Domisili Usaha',
                'template_html' => file_get_contents(resource_path('views/templates/surat-keterangan-domisili.html')),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_surat' => 'Default',
                'template_html' => file_get_contents(resource_path('views/templates/default.html')),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('template_surat')->insert($templates);
    }
}
