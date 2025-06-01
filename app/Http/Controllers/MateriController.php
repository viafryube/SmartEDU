<?php

namespace App\Http\Controllers;

use App\Models\EditorAccess; // Mengimpor model EditorAccess untuk penggunaan dalam controller
use App\Models\Kelas; // Mengimpor model Kelas untuk penggunaan dalam controller
use App\Models\KelasMapel; // Mengimpor model KelasMapel untuk penggunaan dalam controller
use App\Models\Mapel; // Mengimpor model Mapel untuk penggunaan dalam controller
use App\Models\Materi; // Mengimpor model Materi untuk penggunaan dalam controller
use App\Models\MateriFile; // Mengimpor model MateriFile untuk penggunaan dalam controller
use App\Models\User; // Mengimpor model User untuk penggunaan dalam controller
use Exception; // Mengimpor kelas Exception untuk penanganan kesalahan
use Illuminate\Http\Request; // Mengimpor kelas Request dari framework Laravel
use Illuminate\Support\Facades\DB;
// Mengimpor fasad DB untuk penggunaan dalam controller

/**
 * Class : MateriController
 *
 * Class ini berisi berbagai fungsi yang berkaitan dengan manipulasi data-data materi, terutama terkait dengan model.
 */
class MateriController extends Controller
{
    /**
     * Menampilkan halaman Tambah Materi.
     *
     * @param  string  $token // Parameter token untuk dekripsi
     * @return \Illuminate\View\View // Mengembalikan tampilan dalam bentuk view Laravel
     */
    public function viewCreateMateri($token, Request $request)
    {
        // id = Kelas Id
        $id = decrypt($token); // Melakukan dekripsi pada token untuk mendapatkan ID Kelas
        $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $id)->first(); // Mengambil data KelasMapel berdasarkan mapel_id dan kelas_id

        $preparedIdMateri = count(Materi::get()); // Menghitung jumlah materi yang sudah ada
        $preparedIdMateri = $preparedIdMateri + 1; // Menyiapkan ID materi berikutnya

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $kelasMapel['id']) { // Jika pengguna memiliki akses editor pada KelasMapel yang sesuai
                $roles = DashboardController::getRolesName(); // Mendapatkan daftar peran
                $mapel = Mapel::where('id', $request->mapelId)->first(); // Mendapatkan data mapel

                $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang sudah ditugaskan

                return view('menu.pengajar.materi.viewTambahMateri', ['assignedKelas' => $assignedKelas, 'title' => 'Tambah Materi', 'roles' => $roles, 'kelasId' => $id, 'mapel' => $mapel, 'preparedIdMateri' => $preparedIdMateri]);
            }
        }
        abort(404); // Menampilkan halaman error 404 jika tidak ada akses editor yang sesuai
    }

    /**
     * Menampilkan halaman Update Materi.
     *
     * @param  string  $token // Parameter token untuk dekripsi
     * @return \Illuminate\View\View // Mengembalikan tampilan dalam bentuk view Laravel
     */
    public function viewUpdateMateri($token, Request $request)
    {
        // token = Materi Id
        $id = decrypt($token); // Melakukan dekripsi pada token untuk mendapatkan ID Materi
        $materi = Materi::where('id', $id)->first(); // Mengambil data Materi berdasarkan ID

        // Dapatkan kelas mapel untuk dibandingkan dengan materi
        $kelasMapel = KelasMapel::where('id', $materi->kelas_mapel_id)->first(); // Mengambil data KelasMapel berdasarkan ID yang terdapat pada Materi

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $kelasMapel['id']) { // Jika pengguna memiliki akses editor pada KelasMapel yang sesuai
                $roles = DashboardController::getRolesName(); // Mendapatkan daftar peran
                $mapel = Mapel::where('id', $request->mapelId)->first(); // Mendapatkan data mapel

                $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id'); // Mendapatkan data kelas

                $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang sudah ditugaskan

                return view('menu.pengajar.materi.viewUpdateMateri', ['assignedKelas' => $assignedKelas, 'title' => 'Update Materi', 'materi' => $materi, 'roles' => $roles, 'kelasId' => $kelas['id'], 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]);
            }
        }
        abort(404); // Menampilkan halaman error 404 jika tidak ada akses editor yang sesuai
    }

    /**
     * Menampilkan halaman Materi.
     *
     * @return \Illuminate\View\View // Mengembalikan tampilan dalam bentuk view Laravel
     */
    public function viewMateri(Request $request)
    {
        // materi id
        $id = decrypt($request->token); // Melakukan dekripsi pada token untuk mendapatkan ID Materi
        //kelasMapel id
        $idx = decrypt($request->kelasMapelId); // Melakukan dekripsi pada kelasMapelId untuk mendapatkan ID KelasMapel

        $materi = Materi::where('id', $id)->first(); // Mengambil data Materi berdasarkan ID

        $roles = DashboardController::getRolesName(); // Mendapatkan daftar peran
        $kelasMapel = KelasMapel::where('id', $materi->kelas_mapel_id)->first(); // Mengambil data KelasMapel berdasarkan ID yang terdapat pada Materi

        // Dapatkan Pengajar
        $editorAccess = EditorAccess::where('kelas_mapel_id', $kelasMapel['id'])->first(); // Mengambil data EditorAccess berdasarkan ID KelasMapel
        $editorData = User::where('id', $editorAccess['user_id'])->where('roles_id', 2)->first(); // Mendapatkan data pengguna yang memiliki peran pengajar

        $mapel = Mapel::where('id', $request->mapelId)->first(); // Mendapatkan data mapel
        $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id'); // Mendapatkan data kelas

        $materiAll = Materi::where('kelas_mapel_id', $idx)->get(); // Mengambil semua materi berdasarkan ID KelasMapel

        $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang sudah ditugaskan

        return view('menu.pengajar.materi.viewMateri', ['assignedKelas' => $assignedKelas, 'editor' => $editorData, 'materi' => $materi, 'kelas' => $kelas, 'title' => $materi->name, 'roles' => $roles, 'materiAll' => $materiAll, 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]);
    }

    /**
     * Membuat Materi baru.
     *
     * @return \Illuminate\Http\RedirectResponse // Mengembalikan respons redirect Laravel
     */
    public function createMateri(Request $request)
    {
        // Lakukan validasi untuk inputan form
        $request->validate([
            'name' => 'required', // Nama materi wajib diisi
            'content' => 'required', // Konten materi wajib diisi
        ]);

        try {
            // Dekripsi token dan dapatkan KelasMapel
            $token = decrypt($request->kelasId); // Melakukan dekripsi pada token untuk mendapatkan ID Kelas
            $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $token)->first(); // Mengambil data KelasMapel berdasarkan mapel_id dan kelas_id

            $isHidden = 1;

            if ($request->opened) {
                $isHidden = 0;
            }
            $temp = [
                'kelas_mapel_id' => $kelasMapel['id'], // Mendapatkan ID KelasMapel
                'name' => $request->name, // Mendapatkan nama materi
                'content' => $request->content, // Mendapatkan konten materi
                'isHidden' => $isHidden, // Mendapatkan status isHidden
            ];

            // Simpan data Materi ke database
            Materi::create($temp); // Menyimpan data materi baru ke dalam database

            // Commit transaksi database
            DB::commit(); // Melakukan commit transaksi ke database

            // Berikan respons sukses jika semuanya berjalan lancar
            return response()->json(['message' => 'Materi berhasil dibuat'], 200); // Mengembalikan respons JSON dengan pesan sukses
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200); // Mengembalikan respons JSON dengan pesan error
        }
    }

    /**
     * Mengupdate Materi.
     *
     * @return \Illuminate\Http\RedirectResponse // Mengembalikan respons redirect Laravel
     */
    public function updateMateri(Request $request)
    {
        // Lakukan validasi untuk inputan form
        $request->validate([
            'name' => 'required', // Nama materi wajib diisi
            'content' => 'required', // Konten materi wajib diisi
        ]);
        // return response()->json(['message' => $request->input()], 200);
        // Dekripsi token hasil dari hidden form lalu dapatkan data KelasMapel
        $materiId = decrypt($request->materiId); // Melakukan dekripsi pada materiId untuk mendapatkan ID Materi

        try {
            $isHidden = 1;

            if ($request->opened) {
                $isHidden = 0;
            }
            $data = [
                'name' => $request->name, // Mendapatkan nama materi dari inputan form
                'content' => $request->content, // Mendapatkan konten materi dari inputan form
                'isHidden' => $isHidden, // Mendapatkan status isHidden dari inputan form
            ];
            // Simpan data Materi ke database
            Materi::where('id', $materiId)->update($data); // Mengupdate data materi ke dalam database
            // Commit transaksi database
            DB::commit(); // Melakukan commit transaksi ke database

            // Berikan respons sukses jika semuanya berjalan lancar
            return response()->json(['message' => 'Materi berhasil dibuat'], 200); // Mengembalikan respons JSON dengan pesan sukses
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200); // Mengembalikan respons JSON dengan pesan error
        }
    }

    /**
     * Menghapus Materi.
     *
     * @return \Illuminate\Http\RedirectResponse // Mengembalikan respons redirect Laravel
     */
    public function destroyMateri(Request $request)
    {

        // Dapatkan Id Materi dari Inputan Form request
        $materiId = $request->hapusId; // Mendapatkan ID Materi dari inputan form

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $request->kelasMapelId) { // Jika pengguna memiliki akses editor pada KelasMapel yang sesuai
                $dest = '../public_html/file/materi'; // Destinasi tempat pengguna akan disimpan
                $files = MateriFile::where('materi_id', $materiId)->get(); // Mengambil data file-file terkait materi
                foreach ($files as $key) {
                    if (file_exists(public_path($dest . '/' . $key->file))) {
                        unlink(public_path($dest . '/' . $key->file)); // Menghapus file-file terkait materi dari direktori
                    }
                }
                Materi::where('id', $materiId)->delete(); // Menghapus data Materi dari database
                MateriFile::where('materi_id', $materiId)->delete(); // Menghapus data file-file terkait materi dari database

                return redirect()->back()->with('success', 'Materi Berhasil dihapus'); // Mengembalikan respons redirect dengan pesan sukses
            }
        }
        abort(404); // Menampilkan halaman error 404 jika tidak ada akses editor yang sesuai
    }

    /**
     * Upload file Materi.
     *
     * @return \Illuminate\Http\RedirectResponse // Mengembalikan respons redirect Laravel
     */
    public function uploadFileMateri(Request $request)
    {
        // Dapatkan Id Materi dari Inputan Form request
        // Validasi file yang diunggah
        $request->validate([
            'file' => 'required|file|max:2048', // Batasan ukuran maksimum adalah 2 MB (ganti sesuai kebutuhan Anda)
        ]);

        // return response()->json(['message' => $request->input()]);
        if ($request->action == 'tambah') {
            $latestMateri = Materi::latest()->first(); // Mendapatkan data materi terbaru

            // Proses unggahan file di sini
            $file = $request->file('file'); // Mendapatkan file yang diunggah
            $fileName = 'F' . mt_rand(1, 999) . '_' . $file->getClientOriginalName(); // Generate nama file unik
            $file->move(storage_path('app/public/file/materi'), $fileName); // Simpan file di direktori 'storage/app/uploads'

            MateriFile::create([ // Membuat entri baru pada tabel MateriFile
                'materi_id' => $latestMateri['id'], // Mendapatkan ID Materi
                'file' => $fileName, // Menyimpan nama file
            ]);

            return response()->json(['message' => 'File berhasil diunggah.']); // Respon sukses
        } elseif ($request->action == 'edit') {
            // Proses unggahan file di sini
            $file = $request->file('file'); // Mendapatkan file yang diunggah
            $fileName = 'F' . mt_rand(1, 999) . '_' . $file->getClientOriginalName(); // Generate nama file unik
            $file->move(storage_path('app/public/file/materi'), $fileName); // Simpan file di direktori 'storage/app/uploads'

            MateriFile::create([ // Membuat entri baru pada tabel MateriFile
                'materi_id' => $request->idMateri, // Mendapatkan ID Materi
                'file' => $fileName, // Menyimpan nama file
            ]);

            return response()->json(['message' => 'File berhasil diunggah.']); // Respon sukses
        }

        return response()->json(['message' => 'File Error.']); // Respon error jika terjadi kesalahan
    }

    /**
     * Delete file Materi.
     *
     * @return \Illuminate\Http\RedirectResponse // Mengembalikan respons redirect Laravel
     */
    public function destroyFileMateri(Request $request)
    {
        $idMateri = $request->idMateri; // Mendapatkan ID Materi dari inputan form
        $fileName = $request->fileName; // Mendapatkan nama file dari inputan form

        $dest = 'file/materi'; // Destinasi tempat pengguna akan disimpan

        if (file_exists(public_path($dest . '/' . $fileName))) {
            unlink(public_path($dest . '/' . $fileName)); // Menghapus file dari direktori
        }

        MateriFile::where('materi_id', $idMateri)->where('file', $fileName)->delete(); // Menghapus entri file dari database

        return redirect()->back()->with('success', 'File Deleted'); // Mengembalikan respons redirect dengan pesan sukses
    }

    /**
     * Redirect back.
     *
     * @return \Illuminate\Http\RedirectResponse // Mengembalikan respons redirect Laravel
     */
    public function redirectBack(Request $request)
    {
        $mapelId = request('amp;mapelId'); // Mendapatkan ID mapel dari query string
        $message = request('amp;message'); // Mendapatkan pesan dari query string

        return redirect(route('viewKelasMapel', ['mapel' => $mapelId, 'token' => encrypt($request->kelasId), 'mapel_id' => $mapelId]))->with('success', 'Data Berhasil di ' . $message); // Mengembalikan respons redirect dengan pesan sukses
    }
}
