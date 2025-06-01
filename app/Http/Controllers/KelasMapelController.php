<?php

namespace App\Http\Controllers; // Mendeklarasikan namespace untuk controller

use App\Exports\NilaiTugasExport; // Mengimpor kelas NilaiTugasExport
use App\Exports\NilaiUjianExport; // Mengimpor kelas NilaiUjianExport
use App\Models\Kelas; // Mengimpor model Kelas
use App\Models\KelasMapel; // Mengimpor model KelasMapel
use App\Models\Mapel; // Mengimpor model Mapel
use App\Models\Materi; // Mengimpor model Materi
use App\Models\Rekomendasi; // Mengimpor model Rekomendasi
use App\Models\Pengumuman; // Mengimpor model Pengumuman
use App\Models\Diskusi; // Mengimpor model Diskusi
use App\Models\Tugas; // Mengimpor model Tugas
use App\Models\Ujian; // Mengimpor model Ujian
use App\Models\User; // Mengimpor model User
use Illuminate\Http\Request; // Mengimpor kelas Request
use Maatwebsite\Excel\Facades\Excel; // Mengimpor facade Excel

class KelasMapelController extends Controller // Mendeklarasikan kelas controller KelasMapel
{
    /**
     * Menampilkan halaman kelas dan mata pelajaran tertentu.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewKelasMapel($x, $token, Request $request)
    {
        if ($token) { // Memeriksa apakah token ada
            $id = decrypt($token); // Mendekripsi token menjadi ID kelas
            $mapel = Mapel::where('id', $request->mapel_id)->first(); // Mengambil data mapel berdasarkan ID
            $kelas = Kelas::where('id', $id)->first(); // Mengambil data kelas berdasarkan ID
            $kelasMapel = KelasMapel::where('mapel_id', $request->mapel_id)->where('kelas_id', $id)->first(); // Mengambil data KelasMapel berdasarkan mapel_id dan kelas_id
            $materi = Materi::where('kelas_mapel_id', $kelasMapel['id'])->get(); // Mengambil semua materi terkait kelasMapel
            $rekomendasi = Rekomendasi::where('kelas_mapel_id', $kelasMapel['id'])->get(); // Mengambil semua rekomendasi terkait kelasMapel
            $pengumuman = Pengumuman::where('kelas_mapel_id', $kelasMapel['id'])->get(); // Mengambil semua pengumuman terkait kelasMapel
            $diskusi = Diskusi::where('kelas_mapel_id', $kelasMapel['id'])->get(); // Mengambil semua diskusi terkait kelasMapel
            $tugas = Tugas::where('kelas_mapel_id', $kelasMapel['id'])->get(); // Mengambil semua tugas terkait kelasMapel
            $ujian = Ujian::where('kelas_mapel_id', $kelasMapel['id'])->get(); // Mengambil semua ujian terkait kelasMapel
            $roles = DashboardController::getRolesName(); // Mendapatkan peran pengguna
            $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang ditugaskan kepada pengguna
            $editor = null; // Inisialisasi variabel editor

            // Editor Data
            if (count($kelasMapel->EditorAccess) > 0) { // Memeriksa apakah ada EditorAccess untuk kelasMapel
                $editor = User::where('id', $kelasMapel->EditorAccess[0]->user_id)->first(); // Mengambil data pengguna yang menjadi editor
                $editor = [
                    'name' => $editor['name'], // Menyimpan nama editor
                    'id' => $editor['id'], // Menyimpan ID editor
                ];
            }

            // Mengembalikan tampilan viewKelasMapel dengan data yang diperlukan
            return view('menu.kelasMapel.viewKelasMapel', ['editor' => $editor, 'assignedKelas' => $assignedKelas, 'diskusi' => $diskusi, 'pengumuman' => $pengumuman, 'roles' => $roles, 'title' => 'Dashboard', 'kelasMapel' => $kelasMapel, 'ujian' => $ujian, 'materi' => $materi, 'mapel' => $mapel, 'kelas' => $kelas, 'tugas' => $tugas, 'rekomendasi' => $rekomendasi]);
        } else {
            abort(404); // Mengembalikan halaman 404 jika token tidak ada
        }
    }

    public function viewAllActivities()
    {
        // Ambil semua materi dan pengumuman
        $materi = Materi::all(); // Mengambil semua data materi
        $pengumuman = Pengumuman::all(); // Mengambil semua data pengumuman
        $rekomendasi = Rekomendasi::all(); // Mengambil semua data rekomendasi
        $diskusi = Diskusi::all(); // Mengambil semua data diskusi
        $tugas = Tugas::all(); // Mengambil semua data tugas
        $ujian = Ujian::all(); // Mengambil semua data ujian
        $roles = DashboardController::getRolesName(); // Mendapatkan peran pengguna
        
        // Ambil semua kelasMapel
        $kelasMapel = KelasMapel::all(); // Mengambil semua data kelasMapel
        
        // Array untuk menyimpan data editor
        $editors = []; // Inisialisasi array editor
    
        foreach ($kelasMapel as $km) { // Loop melalui setiap kelasMapel
            if (count($km->EditorAccess) > 0) { // Memeriksa apakah ada EditorAccess untuk kelasMapel
                $editor = User::where('id', $km->EditorAccess[0]->user_id)->first(); // Mengambil data pengguna yang menjadi editor
                $editors[$km->id] = [
                    'name' => $editor->name, // Menyimpan nama editor
                    'id' => $editor->id, // Menyimpan ID editor
                ];
            } else {
                $editors[$km->id] = null; // Menyimpan null jika tidak ada editor
            }
        }
    
        // Mengembalikan tampilan activity dengan data yang diperlukan
        return view('menu.admin.activity', [
            'materi' => $materi,
            'pengumuman' => $pengumuman,
            'rekomendasi' => $rekomendasi,
            'diskusi' => $diskusi,
            'tugas' => $tugas,
            'ujian' => $ujian,
            'title' => 'Activity',
            'roles' => $roles,
            'editors' => $editors
        ]);
    }
    

    /**
     * Metode untuk menyimpan gambar sementara.
     *
     * @return \Illuminate\View\View
     */
    public function saveImageTemp(Request $request)
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan peran pengguna
        $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang ditugaskan kepada pengguna

        // Mengembalikan tampilan viewKelasMapel dengan data yang diperlukan
        return view('menu.mapelKelas.viewKelasMapel', ['assignedKelas' => $assignedKelas, 'roles' => $roles, 'title' => 'Dashboard']);
    }

    public function exportNilaiTugas(Request $request)
    {
        // Mengunduh data nilai tugas dalam format Excel
        return Excel::download(new NilaiTugasExport($request->tugasId, $request->kelasMapelId), 'export-kelas.xls');
    }

    public function exportNilaiUjian(Request $request)
    {
        // Mengunduh data nilai ujian dalam format Excel
        return Excel::download(new NilaiUjianExport($request->ujianId, $request->kelasMapelId), 'export-kelas.xls');
    }
}
