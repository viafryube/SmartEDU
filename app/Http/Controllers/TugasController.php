<?php

namespace App\Http\Controllers;

use App\Models\EditorAccess; // Menggunakan model EditorAccess
use App\Models\Kelas; // Menggunakan model Kelas
use App\Models\KelasMapel; // Menggunakan model KelasMapel
use App\Models\Mapel; // Menggunakan model Mapel
use App\Models\Tugas; // Menggunakan model Tugas
use App\Models\TugasFile; // Menggunakan model TugasFile
use App\Models\User; // Menggunakan model User
use App\Models\UserTugas; // Menggunakan model UserTugas
use App\Models\UserTugasFile; // Menggunakan model UserTugasFile
use Exception; // Menggunakan kelas Exception
use Illuminate\Http\Request; // Menggunakan kelas Request dari Illuminate
use Illuminate\Support\Facades\DB; // Menggunakan DB dari Illuminate\Support\Facades

class TugasController extends Controller
{
    /**
     * Menampilkan halaman Tugas.
     *
     * @return \Illuminate\View\View
     */
    public function viewTugas(Request $request) // Fungsi untuk menampilkan halaman Tugas
    {
        // Tugas id
        $id = decrypt($request->token); // Mendekripsi token dari request
        //kelasMapel id
        $idx = decrypt($request->kelasMapelId); // Mendekripsi kelasMapelId dari request

        $tugas = Tugas::where('id', $id)->first(); // Mengambil data Tugas berdasarkan id

        $roles = DashboardController::getRolesName(); // Mendapatkan nama roles dari DashboardController
        $kelasMapel = KelasMapel::where('id', $tugas->kelas_mapel_id)->first(); // Mengambil data KelasMapel berdasarkan id

        // Dapatkan Pengajar
        $editorAccess = EditorAccess::where('kelas_mapel_id', $kelasMapel['id'])->first(); // Mendapatkan akses editor berdasarkan kelasMapel
        $editorData = User::where('id', $editorAccess['user_id'])->where('roles_id', 2)->first(); // Mendapatkan data pengguna dengan roles_id 2

        $mapel = Mapel::where('id', $request->mapelId)->first(); // Mengambil data Mapel berdasarkan mapelId dari request
        $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first(); // Mengambil data Kelas berdasarkan kelasMapel

        $tugasAll = Tugas::where('kelas_mapel_id', $idx)->get(); // Mengambil semua data Tugas berdasarkan kelasMapel id

        $userTugas = UserTugas::where('tugas_id', $tugas['id'])->where('user_id', Auth()->User()->id)->first(); // Mendapatkan data UserTugas berdasarkan tugas_id dan user_id

        $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang di-assign

        return view('menu.pengajar.tugas.viewTugas', ['userTugas' => $userTugas, 'assignedKelas' => $assignedKelas, 'editor' => $editorData, 'tugas' => $tugas, 'kelas' => $kelas, 'title' => $tugas->name, 'roles' => $roles, 'tugasAll' => $tugasAll, 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]); // Menampilkan view dengan data yang diperlukan
    }
    public function viewTugasAdmin(Request $request) // Fungsi untuk menampilkan halaman Tugas Admin
    {
        // Tugas id
        $id = decrypt($request->token); // Mendekripsi token dari request
        //kelasMapel id
        $idx = decrypt($request->kelasMapelId); // Mendekripsi kelasMapelId dari request

        $tugas = Tugas::where('id', $id)->first(); // Mengambil data Tugas berdasarkan id

        $roles = DashboardController::getRolesName(); // Mendapatkan nama roles dari DashboardController
        $kelasMapel = KelasMapel::where('id', $tugas->kelas_mapel_id)->first(); // Mengambil data KelasMapel berdasarkan id

        // Dapatkan Pengajar
        $editorAccess = EditorAccess::where('kelas_mapel_id', $kelasMapel['id'])->first(); // Mendapatkan akses editor berdasarkan kelasMapel
        $editorData = User::where('id', $editorAccess['user_id'])->where('roles_id', 2)->first(); // Mendapatkan data pengguna dengan roles_id 2

        $mapel = Mapel::where('id', $request->mapelId)->first(); // Mengambil data Mapel berdasarkan mapelId dari request
        $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first(); // Mengambil data Kelas berdasarkan kelasMapel

        $tugasAll = Tugas::where('kelas_mapel_id', $idx)->get(); // Mengambil semua data Tugas berdasarkan kelasMapel id

        $userTugas = UserTugas::where('tugas_id', $tugas['id'])->where('user_id', Auth()->User()->id)->first(); // Mendapatkan data UserTugas berdasarkan tugas_id dan user_id

        $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang di-assign

        return view('menu.pengajar.tugas.viewTugasAdmin', ['userTugas' => $userTugas, 'assignedKelas' => $assignedKelas, 'editor' => $editorData, 'tugas' => $tugas, 'kelas' => $kelas, 'title' => $tugas->name, 'roles' => $roles, 'tugasAll' => $tugasAll, 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]); // Menampilkan view dengan data yang diperlukan
    }

    /**
     * Menampilkan halaman Tugas.
     *
     * @return \Illuminate\View\View
     */
    public function siswaUpdateNilai(Request $request) // Fungsi untuk mengupdate nilai siswa
    {
        $id = decrypt($request->token); // Mendekripsi token dari request

        // Request SiswaId[], nilai[],

        // $userTugas = UserTugas::where('tugas_id', $id)->get();

        // Looping semua nilai user inputan
        for ($i = 0; $i < count($request->nilai); $i++) { // Looping untuk setiap nilai yang diinputkan
            // Memeriksa apakah nilai tidak sama dengan null dan tidak sama dengan string kosong
            if ($request->nilai[$i] !== null && $request->nilai[$i] !== '') { // Memeriksa apakah nilai valid
                $exist = UserTugas::where('tugas_id', $id)->where('user_id', $request->siswaId[$i])->first(); // Memeriksa apakah data UserTugas sudah ada

                // Nilai Cap
                $nilai = $request->nilai[$i]; // Mengambil nilai

                if ($nilai >= 100) { // Memeriksa apakah nilai lebih dari atau sama dengan 100
                    $nilai = 100; // Set nilai menjadi 100
                } elseif ($nilai <= 0) { // Memeriksa apakah nilai kurang dari atau sama dengan 0
                    $nilai = 0; // Set nilai menjadi 0
                }

                // dd($exist);
                if ($exist) { // Jika data UserTugas sudah ada
                    $data = [ // Data yang akan diupdate
                        'status' => 'Telah dinilai',
                        'nilai' => $nilai,
                    ];
                    $exist->update($data); // Update data UserTugas
                } else { // Jika data UserTugas belum ada
                    $data = [ // Data yang akan disimpan
                        'tugas_id' => $id,
                        'user_id' => $request->siswaId[$i],
                        'status' => 'Telah dinilai',
                        'nilai' => $nilai,
                    ];
                    UserTugas::create($data); // Simpan data UserTugas baru
                }
            }
        }

        return redirect()->back()->with('success', 'Nilai Telah diPerbaharui'); // Redirect kembali dengan pesan sukses
    }

    /**
     * Menampilkan halaman Tambah Tugas.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewCreateTugas($token, Request $request) // Fungsi untuk menampilkan halaman tambah Tugas
    {
        // id = Kelas Id
        $id = decrypt($token); // Mendekripsi token dari parameter
        $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $id)->first(); // Mengambil data KelasMapel berdasarkan mapelId dan kelasId

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) { // Looping untuk setiap EditorAccess pengguna
            if ($key->kelas_mapel_id == $kelasMapel['id']) { // Memeriksa apakah pengguna memiliki akses editor pada kelasMapel tertentu
                $roles = DashboardController::getRolesName(); // Mendapatkan nama roles dari DashboardController
                $mapel = Mapel::where('id', $request->mapelId)->first(); // Mengambil data Mapel berdasarkan mapelId dari request

                $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang di-assign

                return view('menu.pengajar.tugas.viewTambahTugas', ['assignedKelas' => $assignedKelas, 'title' => 'Tambah Tugas', 'roles' => $roles, 'kelasId' => $id, 'mapel' => $mapel]); // Menampilkan view dengan data yang diperlukan
            }
        }
        abort(404); // Menghentikan proses dan menampilkan error 404
    }

    /**
     * Menampilkan halaman Update Tugas.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewUpdateTugas($token, Request $request) // Fungsi untuk menampilkan halaman update Tugas
    {
        // token = Tugas Id
        $id = decrypt($token); // Mendekripsi token dari parameter
        $tugas = Tugas::where('id', $id)->first();  // Dapatkan tugas

        // Dapatkan kelas mapel untuk dibandingkan dengan tugas
        $kelasMapel = KelasMapel::where('id', $tugas->kelas_mapel_id)->first(); // Mengambil data KelasMapel berdasarkan id tugas

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) { // Looping untuk setiap EditorAccess pengguna
            if ($key->kelas_mapel_id == $kelasMapel['id']) { // Memeriksa apakah pengguna memiliki akses editor pada kelasMapel tertentu
                $roles = DashboardController::getRolesName(); // Mendapatkan nama roles dari DashboardController
                $mapel = Mapel::where('id', $request->mapelId)->first(); // Mengambil data Mapel berdasarkan mapelId dari request

                $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id'); // Mengambil data Kelas berdasarkan kelasMapel

                $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang di-assign

                return view('menu.pengajar.tugas.viewUpdateTugas', ['assignedKelas' => $assignedKelas, 'title' => 'Update Tugas', 'tugas' => $tugas, 'roles' => $roles, 'kelasId' => $kelas['id'], 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]); // Menampilkan view dengan data yang diperlukan
            }
        }
        abort(404); // Menghentikan proses dan menampilkan error 404
    }

    /**
     * Membuat Tugas baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createTugas(Request $request) // Fungsi untuk membuat Tugas baru
    {
        // Lakukan validasi untuk inputan form
        // return response()->json(['message' => $request->input()], 200);

        $request->validate([ // Validasi inputan form
            'name' => 'required',
            'content' => 'required',
            'due' => 'required',
        ]);
        // return response()->json(['message' => $request->due], 200);
        $tanggalWaktuIndonesia = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->due); // Mengubah format tanggal dan waktu

        try {
            // Dekripsi token dan dapatkan KelasMapel
            $token = decrypt($request->kelasId); // Mendekripsi token dari request
            $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $token)->first(); // Mengambil data KelasMapel

            $isHidden = 1;

            if ($request->opened) { // Jika tugas dibuka
                $isHidden = 0;
            }

            $temp = [ // Data tugas baru
                'kelas_mapel_id' => $kelasMapel['id'],
                'name' => $request->name,
                'content' => $request->content,
                'due' => $tanggalWaktuIndonesia,
                'isHidden' => $isHidden,
            ];

            // Simpan data Tugas ke database
            Tugas::create($temp); // Membuat data Tugas baru

            // Commit transaksi database
            DB::commit(); // Melakukan commit transaksi database

            // Berikan respons sukses jika semuanya berjalan lancar
            return response()->json(['message' => 'Tugas berhasil dibuat'], 200); // Respon sukses
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200); // Respon error
        }
    }

    /**
     * Mengupdate Tugas.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTugas(Request $request) // Fungsi untuk mengupdate Tugas
    {
        // Lakukan validasi untuk inputan form
        $request->validate([ // Validasi inputan form
            'name' => 'required',
            'content' => 'required',
            'due' => 'required',
        ]);

        // return response()->json(['message' => $request->due], 200);
        // return response()->json(['message' => $request->input()], 200);

        $tanggalWaktuIndonesia = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->due);

        // return response()->json(['message' => $tanggalWaktuIndonesia], 200);
        // Dekripsi token hasil dari hidden form lalu dapatkan data KelasMapel
        $tugasId = decrypt($request->tugasId);

        try {
            $isHidden = 1;

            if ($request->opened) {
                $isHidden = 0;
            }

            $data = [
                'name' => $request->name,
                'content' => $request->content,
                'due' => $tanggalWaktuIndonesia,
                'isHidden' => $isHidden,
            ];
            // Simpan data Tugas ke database
            Tugas::where('id', $tugasId)->update($data);
            // Commit transaksi database
            DB::commit();

            // Berikan respons sukses jika semuanya berjalan lancar
            return response()->json(['message' => 'Tugas berhasil dibuat'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200);
        }
    }

    /**
     * Membuat Tugas baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadFileTugas(Request $request)
    {
        // Dapatkan Id Tugas dari Inputan Form request
        // Validasi file yang diunggah
        $request->validate([
            'file' => 'required|file|max:2048', // Batasan ukuran maksimum adalah 2 MB (ganti sesuai kebutuhan Anda)
        ]);

        // return response()->json(['message' => $request->input()]);
        if ($request->action == 'tambah') {
            $latesTugas = Tugas::latest()->first();

            // Proses unggahan file di sini
            $file = $request->file('file');
            $fileName = 'F' . mt_rand(1, 999) . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/file/tugas'), $fileName); // Simpan file di direktori 'storage/app/uploads'

            TugasFile::create([
                'tugas_id' => $latesTugas['id'],
                'file' => $fileName,
            ]);

            return response()->json(['message' => 'File berhasil diunggah.']); // Respon sukses
        } elseif ($request->action == 'edit') {
            // Proses unggahan file di sini
            $file = $request->file('file');
            $fileName = 'F' . mt_rand(1, 999) . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/file/tugas'), $fileName); // Simpan file di direktori 'storage/app/uploads'

            TugasFile::create([
                'tugas_id' => $request->idTugas,
                'file' => $fileName,
            ]);

            return response()->json(['message' => 'File berhasil diunggah.']); // Respon sukses
        }

        return response()->json(['message' => 'File Error.']);
    }

    /**
     * Delete file Tugas.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyFileTugas(Request $request)
    {
        $idTugas = $request->idTugas;
        $fileName = $request->fileName;

        $dest = '../public_html/file/tugas'; // Destinasi tempat pengguna akan disimpan
        // $dest = 'file/tugas'; // Destinasi untuk Localhost

        if (file_exists(public_path($dest . '/' . $fileName))) {
            unlink(public_path($dest . '/' . $fileName));
        }

        TugasFile::where('tugas_id', $idTugas)->where('file', $fileName)->delete();

        return redirect()->back()->with('success', 'File Deleted');
    }

    /**
     * Menghapus tugas.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyTugas(Request $request)
    {

        // Dapatkan Id tugas dari Inputan Form request
        $tugasId = $request->hapusId;

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $request->kelasMapelId) {
                $dest = '../public_html/file/tugas'; // Destinasi tempat pengguna akan disimpan
                // $dest = 'file/tugas'; // Destinasi tempat pengguna akan disimpan (localhost)
                $files = TugasFile::where('tugas_id', $tugasId)->get();
                foreach ($files as $key) {
                    if (file_exists(public_path($dest . '/' . $key->file))) {
                        unlink(public_path($dest . '/' . $key->file));
                    }
                }
                Tugas::where('id', $tugasId)->delete();
                TugasFile::where('tugas_id', $tugasId)->delete();
                UserTugas::where('tugas_id', $tugasId)->delete();

                return redirect()->back()->with('success', 'Tugas Berhasil dihapus');
            }
        }
        abort(404);
    }

    public function submitTugas(Request $request)
    {

        $tugasId = decrypt($request->tugasId);
        $userId = decrypt($request->userId);

        $data = [
            'tugas_id' => $tugasId,
            'user_id' => $userId,
            'status' => 'Selesai',
        ];
        $userTugas = UserTugas::create($data);

        return response()->json(['message' => 'File berhasil diunggah.']); // Respon sukses
    }

    public function submitFileTugas(Request $request)
    {

        $request->validate([
            'file' => 'required|file|max:2048', // Batasan ukuran maksimum adalah 2 MB (ganti sesuai kebutuhan Anda)
        ]);

        $tugasId = decrypt($request->tugasId);
        $userId = Auth()->User()->id;

        $tugas = Tugas::where('id', $tugasId)->first();

        $dueDateTime = \Carbon\Carbon::parse($tugas->due); // Mengatur timezone ke Indonesia (ID)
        $localTime = \Carbon\Carbon::parse($tugas->due)->setTimeZone('asia/jakarta'); // Mengatur timezone ke Indonesia (ID)
        $now = \Carbon\Carbon::now(); // Mengatur timezone ke Indonesia (ID)
        $timeUntilDue = $dueDateTime->diff($now); // Perbedaan waktu antara sekarang dan waktu jatuh tempo

        if ($dueDateTime > $now) {
            $exist = UserTugas::where('tugas_id', $tugasId)->where('user_id', $userId)->first();

            if ($exist['status'] == 'Belum Mengerjakan') {
                $data = [
                    'status' => 'Selesai',
                ];
                $exist->update($data);
            }

            if ($exist) {
                // Proses unggahan file di sini
                $file = $request->file('file');
                $fileName = 'F' . mt_rand(1, 999) . '_' . $file->getClientOriginalName();
                $file->move(storage_path('app/public/file/tugas/user'), $fileName); // Simpan file di direktori 'storage/app/uploads'
                UserTugasFile::create([
                    'user_tugas_id' => $exist['id'],
                    'file' => $fileName,
                ]);

                return response()->json(['success', 'Upload success']);
            } else {
                return response()->json(['failed', 'Upload failed']);
            }
        } else {
            return response()->json(['404', '404']);
        }
    }

    /**
     * Delete file Tugas.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyFileSubmit(Request $request)
    {
        // $userTugasId = $request->userTugasId;
        $fileName = $request->fileName;

        $userTugasFile = UserTugasFile::where('file', $fileName)->get();
        $userTugas = UserTugas::where('id', $userTugasFile[0]->user_tugas_id)->first();

        $dest = '../public_html/file/tugas'; // Destinasi tempat pengguna akan disimpan

        if (file_exists(public_path($dest . '/' . $fileName))) {
            unlink(public_path($dest . '/' . $fileName));
        }

        UserTugasFile::where('user_tugas_id', $userTugas['id'])->where('file', $fileName)->delete();
        $userTugasCount = UserTugasFile::where('user_tugas_id', $userTugas['id'])->count();

        if ($userTugasCount <= 0) {
            $data = [
                'status' => 'Belum Mengerjakan',
            ];

            UserTugas::where('id', $userTugas['id'])->update($data);
        }

        return redirect()->back()->with('success', 'File Deleted');
    }
}
