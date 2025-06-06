<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // detail_alamats
        DB::table('detail_alamats')->insert([
            ['id' => 1, 'alamat' => 'Jl. Merpati No. 101, Kampung Damai', 'kabupaten' => 'Sleman', 'provinsi' => 'DI Yogyakarta', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'alamat' => 'Jl. Garuda No. 202, Komplek Sejahtera', 'kabupaten' => 'Bantul', 'provinsi' => 'DI Yogyakarta', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'alamat' => 'Jl. Rajawali No. 303, Perumahan Indah', 'kabupaten' => 'Gunung Kidul', 'provinsi' => 'DI Yogyakarta', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'alamat' => 'Jl. Cendrawasih No. 404, Desa Makmur', 'kabupaten' => 'Kulon Progo', 'provinsi' => 'DI Yogyakarta', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'alamat' => 'Jl. Elang No. 505, Asrama Rukun', 'kabupaten' => 'Yogyakarta', 'provinsi' => 'DI Yogyakarta', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // rw
        DB::table('rw')->insert([
            ['id' => 1, 'nama_rw' => 'RW 001', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama_rw' => 'RW 002', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // rt
        DB::table('rt')->insert([
            ['id' => 1, 'id_rw' => 1, 'nama_rt' => 'RT 001', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'id_rw' => 1, 'nama_rt' => 'RT 002', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'id_rw' => 2, 'nama_rt' => 'RT 001', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // users
        DB::table('users')->insert([
            ['id' => 1, 'email' => 'admin.utama@example.com', 'password' => bcrypt('password'), 'role' => 'Admin', 'status_akun' => 1, 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'email' => 'warga.andi@example.com', 'password' => bcrypt('password'), 'role' => 'Warga', 'status_akun' => 1, 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'email' => 'budi.rt@example.com', 'password' => bcrypt('password'), 'role' => 'PejabatRT', 'status_akun' => 1, 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'email' => 'citra.rw1@example.com', 'password' => bcrypt('password'), 'role' => 'PejabatRW', 'status_akun' => 1, 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'email' => 'doni.warga@example.com', 'password' => bcrypt('password'), 'role' => 'Warga', 'status_akun' => 1, 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'email' => 'eka.rw2@example.com', 'password' => bcrypt('password'), 'role' => 'PejabatRW', 'status_akun' => 1, 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'email' => 'fitri.rt3@example.com', 'password' => bcrypt('password'), 'role' => 'PejabatRT', 'status_akun' => 1, 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // wargas
        DB::table('wargas')->insert([
            ['id' => 1, 'id_users' => 2, 'id_alamat' => 1, 'id_rt' => 1, 'nama' => 'Andi Pratama', 'nomor_kk' => '3404010101000001', 'nik' => '3404011010800001', 'jenis_kelamin' => 'Laki-Laki', 'phone' => '081234567890', 'tempat_lahir' => 'Sleman', 'tanggal_lahir' => '1980-10-10', 'agama' => 'Islam', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'id_users' => 3, 'id_alamat' => 2, 'id_rt' => 1, 'nama' => 'Budi Santoso', 'nomor_kk' => '3404010101000002', 'nik' => '3404011505750002', 'jenis_kelamin' => 'Laki-Laki', 'phone' => '081234567891', 'tempat_lahir' => 'Bantul', 'tanggal_lahir' => '1975-05-15', 'agama' => 'Kristen', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'id_users' => 4, 'id_alamat' => 3, 'id_rt' => 2, 'nama' => 'Citra Lestari', 'nomor_kk' => '3404010101000003', 'nik' => '3404012008820003', 'jenis_kelamin' => 'Perempuan', 'phone' => '081234567892', 'tempat_lahir' => 'Gunung Kidul', 'tanggal_lahir' => '1982-08-20', 'agama' => 'Katolik', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'id_users' => 5, 'id_alamat' => 4, 'id_rt' => 3, 'nama' => 'Doni Firmansyah', 'nomor_kk' => '3404010101000004', 'nik' => '3404012511900004', 'jenis_kelamin' => 'Laki-Laki', 'phone' => '081234567893', 'tempat_lahir' => 'Kulon Progo', 'tanggal_lahir' => '1990-11-25', 'agama' => 'Hindu', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'id_users' => 6, 'id_alamat' => 5, 'id_rt' => 3, 'nama' => 'Eka Wahyuni', 'nomor_kk' => '3404010101000005', 'nik' => '3404010507880005', 'jenis_kelamin' => 'Perempuan', 'phone' => '081234567894', 'tempat_lahir' => 'Yogyakarta', 'tanggal_lahir' => '1988-07-05', 'agama' => 'Buddha', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'id_users' => 7, 'id_alamat' => 1, 'id_rt' => 3, 'nama' => 'Fitri Handayani', 'nomor_kk' => '3404010101000006', 'nik' => '3404010203850006', 'jenis_kelamin' => 'Perempuan', 'phone' => '081234567895', 'tempat_lahir' => 'Sleman', 'tanggal_lahir' => '1985-03-02', 'agama' => 'Islam', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // pejabat_rt
        DB::table('pejabat_rt')->insert([
            ['id' => 1, 'id_rt' => 1, 'id_warga' => 2, 'periode_mulai' => 2024, 'periode_selesai' => 2027, 'ttd' => 'ttd_budi_rt.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'id_rt' => 3, 'id_warga' => 6, 'periode_mulai' => 2024, 'periode_selesai' => 2027, 'ttd' => 'ttd_fitri_rt3.png', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // pejabat_rw
        DB::table('pejabat_rw')->insert([
            ['id' => 1, 'id_rw' => 1, 'id_warga' => 3, 'periode_mulai' => 2024, 'periode_selesai' => 2029, 'ttd' => 'ttd_citra_rw1.png', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'id_rw' => 2, 'id_warga' => 5, 'periode_mulai' => 2024, 'periode_selesai' => 2029, 'ttd' => 'ttd_eka_rw2.png', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // detail_pemohon_surats (agama_pemohon was missing, added a default 'Islam')
        DB::table('detail_pemohon_surats')->insert([
            ['id' => 1, 'id_warga' => 1, 'nama_pemohon' => 'Andi Pratama', 'nik_pemohon' => '3404011010800001', 'no_kk_pemohon' => '3404010101000001', 'alamat_pemohon' => 'Jl. Merpati No. 101, Kampung Damai', 'agama_pemohon' => 'Islam', 'phone_pemohon' => '081234567890', 'tempat_tanggal_lahir_pemohon' => 'Sleman, 10-10-1980', 'jenis_kelamin_pemohon' => 'Laki-Laki', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'id_warga' => 4, 'nama_pemohon' => 'Doni Firmansyah', 'nik_pemohon' => '3404012511900004', 'no_kk_pemohon' => '3404010101000004', 'alamat_pemohon' => 'Jl. Cendrawasih No. 404, Desa Makmur', 'agama_pemohon' => 'Hindu', 'phone_pemohon' => '081234567893', 'tempat_tanggal_lahir_pemohon' => 'Kulon Progo, 25-11-1990', 'jenis_kelamin_pemohon' => 'Laki-Laki', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // pengajuan_surats
        DB::table('pengajuan_surats')->insert([
            ['id' => 1, 'id_warga' => 1, 'id_detail_pemohon' => 1, 'id_rt' => 1, 'id_rw' => 1, 'jenis_surat' => 'Surat Keterangan Domisili', 'keterangan' => 'Untuk keperluan administrasi bank.', 'file_surat' => 'domisili_andi.pdf', 'status' => 'Diajukan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'id_warga' => 4, 'id_detail_pemohon' => 2, 'id_rt' => 3, 'id_rw' => 2, 'jenis_surat' => 'Surat Keterangan Tidak Mampu', 'keterangan' => 'Untuk pengajuan beasiswa.', 'file_surat' => 'sktm_doni.pdf', 'status' => 'Diajukan', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // approval_surats
        DB::table('approval_surats')->insert([
            // Assuming 'Pending_RT' is a valid enum value for status_approval.
            // Ensure id_pejabat_rt and id_pejabat_rw correspond to existing records.
            ['id' => 1, 'id_pengajuan' => 1, 'id_rt' => 1, 'id_rw' => 1, 'status_approval' => 'Pending', 'catatan' => 'Menunggu persetujuan RT', 'approved_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'id_pengajuan' => 2, 'id_rt' => 3, 'id_rw' => 2, 'status_approval' => 'Pending', 'catatan' => 'Menunggu persetujuan RT', 'approved_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // program_kerjas
        DB::table('program_kerjas')->insert([
            ['id' => 1, 'id_rw' => 1, 'nama_program_kerja' => 'Kerja Bakti Bulanan RW 001', 'tempat' => 'Area RW 001', 'tanggal_mulai' => '2025-06-15', 'tanggal_selesai' => '2025-06-15', 'waktu_mulai' => '08:00:00', 'waktu_selesai' => '12:00:00', 'penanggung_jawab' => 'Ketua RW 001', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'id_rw' => 2, 'nama_program_kerja' => 'Sosialisasi Kesehatan RW 002', 'tempat' => 'Balai RW 002', 'tanggal_mulai' => '2025-07-10', 'tanggal_selesai' => '2025-07-10', 'waktu_mulai' => '09:00:00', 'waktu_selesai' => '11:00:00', 'penanggung_jawab' => 'Ketua RW 002', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}