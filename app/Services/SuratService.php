<?php

namespace App\Services;

use App\Models\pejabatRT;
use App\Models\pejabatRW;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\PengajuanSurat;
use App\Models\TemplateSurat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SuratService
{
    public function generateSurat(PengajuanSurat $pengajuan)
    {
        Log::info('Generating surat for:', ['pengajuan_id' => $pengajuan->id]);

        $warga = $pengajuan->warga;
        $rt = $pengajuan->rt;
        $rw = $pengajuan->rw;

        $ttd_rt_path = $rt->ttd 
            ? storage_path('app/public/' . $rt->ttd) 
            : public_path('img/placeholder_ttd.png');

        $ttd_rw_path = $rw->ttd 
            ? storage_path('app/public/' . $rw->ttd) 
            : public_path('img/placeholder_ttd.png');

        $jenis_surat = match ($pengajuan->jenis_surat) {
            'Pengantar KTP', 'Keterangan Domisili', 'Surat Domisili Usaha' => $pengajuan->jenis_surat,
            default => 'Default',
        };

        $template = TemplateSurat::where('jenis_surat', $jenis_surat)->firstOrFail();
        $detail_pemohon = $pengajuan->detailPemohon;
        $pejabat_rt = pejabatRT::where('id_rt', $warga->rt->id)->firstOrFail();
        $pejabat_rw = pejabatRW::where('id_rw', $warga->rt->rw->id)->firstOrFail();

        $content = $this->replacePlaceholders($template->template_html, [
            'JENIS_SURAT' => $pengajuan->jenis_surat,
            'KABUPATEN' => "Sleman",
            'KECAMATAN' => "Depok",
            'KELURAHAN' => "Bulaksumur",
            'ALAMAT_KANTOR' => "Bulaksumur, Depok, Sleman Regency, Special Region of Yogyakarta 55281",
            'ID_RT' => $rt->nama_rt,
            'ID_RW' => $rw->nama_rw,
            'NAMA_WARGA' => $detail_pemohon->nama_pemohon,
            'NOMOR_KK' => $detail_pemohon->no_kk_pemohon,
            'NIK_WARGA' => $detail_pemohon->nik_pemohon,
            'ALAMAT_WARGA' => $detail_pemohon->alamat_pemohon,
            'TEMPAT_TGL_LAHIR' => $detail_pemohon->tempat_tanggal_lahir_pemohon,
            'JENIS_KELAMIN' => $detail_pemohon->jenis_kelamin_pemohon,
            'AGAMA' => $detail_pemohon->agama_pemohon,
            'NO_SURAT' => $this->generateNomorSurat($pengajuan),
            'TANGGAL_SURAT' => Carbon::now()->translatedFormat('d F Y'),
            'NAMA_RT' => $pejabat_rt->warga->nama,
            'NAMA_RW' => $pejabat_rw->warga->nama,
            'TTD_RT' => $ttd_rt_path,
            'TTD_RW' => $ttd_rw_path
        ]);

        $pdf = Pdf::loadHTML($content)->setPaper('A4', 'portrait');

        $filename = Str::slug($pengajuan->jenis_surat . '-' . $warga->nama . '-' . time()) . '.pdf';
        $path = 'surat/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    private function generateNomorSurat(PengajuanSurat $pengajuan)
    {
        $prefix = match($pengajuan->jenis_surat) {
            'Pengantar KTP' => 'KTP',
            'Pengantar KK' => 'KK',
            'Pengantar Akta Kelahiran' => 'AK',
            'Surat Keterangan Kematian' => 'SKK',
            'Surat Domisili Tempat tinggal' => 'DTT',
            'Surat Domisili Usaha' => 'DTT',
            'Surat Keterangan Tidak Mampu' => 'SKTM',
            'Surat SKCK' => 'SKCK',
            'Surat Ketenagakerjaan' => 'SKTK',
            'Surat Pengantar Nikah' => 'SPN',
            'Surat Keterangan Pindah' => 'SKP',
            default => 'UMM'
        };

        $count = PengajuanSurat::whereYear('created_at', now()->year)->count() + 1;
        return sprintf('%s/%03d/RT%02d/RW%02d/%s',
            $prefix,
            $count,
            $pengajuan->id_rt,
            $pengajuan->id_rw,
            now()->year
        );
    }

    private function replacePlaceholders($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }
}
