<?php

namespace App\Http\Controllers;

use App\Models\EditorAccess;
use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\Mapel;
use App\Models\Rekomendasi;
use App\Models\RekomendasiFile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class : RekomendasiController
 *
 * Class ini berisi berbagai fungsi yang berkaitan dengan manipulasi data-data Rekomendasi, terutama terkait dengan model.

 */
class RekomendasiController extends Controller
{
    /**
     * Menampilkan halaman Tambah Rekomendasi.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewCreateRekomendasi($token, Request $request)
    {
        // id = Kelas Id
        $id = decrypt($token);
        $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $id)->first();

        $preparedIdRekomendasi = count(Rekomendasi::get());
        $preparedIdRekomendasi = $preparedIdRekomendasi + 1;
        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $kelasMapel['id']) {
                $roles = DashboardController::getRolesName();
                $mapel = Mapel::where('id', $request->mapelId)->first();

                $assignedKelas = DashboardController::getAssignedClass();

                return view('menu.pengajar.rekomendasi.viewTambahRekomendasi', ['assignedKelas' => $assignedKelas, 'title' => 'Tambah Rekomendasi', 'roles' => $roles, 'kelasId' => $id, 'mapel' => $mapel, 'preparedIdRekomendasi' => $preparedIdRekomendasi]);
            }
        }
        abort(404);
    }

    /**
     * Menampilkan halaman Update Rekomendasi.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewUpdateRekomendasi($token, Request $request)
    {
        // token = Rekomendasi Id
        $id = decrypt($token);
         $rekomendasi= Rekomendasi::where('id', $id)->first();  // Dapatkan Rekomendasi

        // Dapatkan kelas mapel untuk dibandingkan dengan Rekomendasi
        $kelasMapel = KelasMapel::where('id', $rekomendasi->kelas_mapel_id)->first();

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $kelasMapel['id']) {
                $roles = DashboardController::getRolesName();
                $mapel = Mapel::where('id', $request->mapelId)->first();

                $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id');

                $assignedKelas = DashboardController::getAssignedClass();

                return view('menu.pengajar.rekomendasi.viewUpdateRekomendasi', ['assignedKelas' => $assignedKelas, 'title' => 'Update Rekomendasi', 'rekomendasi' => $rekomendasi, 'roles' => $roles, 'kelasId' => $kelas['id'], 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]);
            }
        }
        abort(404);
    }

    /**
     * Menampilkan halaman Rekomendasi.
     *
     * @return \Illuminate\View\View
     */
    public function viewRekomendasi(Request $request)
    {
        // Rekomendasi id
        $id = decrypt($request->token);
        //kelasMapel id
        $idx = decrypt($request->kelasMapelId);

        $rekomendasi = Rekomendasi::where('id', $id)->first();

        $roles = DashboardController::getRolesName();
        $kelasMapel = KelasMapel::where('id', $rekomendasi->kelas_mapel_id)->first();

        // Dapatkan Pengajar
        $editorAccess = EditorAccess::where('kelas_mapel_id', $kelasMapel['id'])->first();
        $editorData = User::where('id', $editorAccess['user_id'])->where('roles_id', 2)->first();

        $mapel = Mapel::where('id', $request->mapelId)->first();
        $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id');

        $rekomendasiAll = Rekomendasi::where('kelas_mapel_id', $idx)->get();

        $assignedKelas = DashboardController::getAssignedClass();

        return view('menu.pengajar.rekomendasi.viewRekomendasi', ['assignedKelas' => $assignedKelas, 'editor' => $editorData, 'rekomendasi' => $rekomendasi, 'kelas' => $kelas, 'title' => $rekomendasi->name, 'roles' => $roles, 'rekomendasiAll' => $rekomendasiAll, 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]);
    }

    /**
     * Membuat Rekomendasi baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createRekomendasi(Request $request)
    {
        // Lakukan validasi untuk inputan form
        $request->validate([
            'name' => 'required',
            'content' => 'required',
        ]);

        try {
            // Dekripsi token dan dapatkan KelasMapel
            $token = decrypt($request->kelasId);
            $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $token)->first();

            $isHidden = 1;

            if ($request->opened) {
                $isHidden = 0;
            }
            $temp = [
                'kelas_mapel_id' => $kelasMapel['id'],
                'name' => $request->name,
                'content' => $request->content,
                'isHidden' => $isHidden,
            ];

            // Simpan data Rekomendasi ke database
            Rekomendasi::create($temp);

            // Commit transaksi database
            DB::commit();

            // Berikan respons sukses jika semuanya berjalan lancar
            return response()->json(['message' => 'Rekomendasi berhasil dibuat'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200);
        }
    }

    /**
     * Mengupdate Rekomendasi.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRekomendasi(Request $request)
    {
        // Lakukan validasi untuk inputan form
        $request->validate([
            'name' => 'required',
            'content' => 'required',
        ]);
        // return response()->json(['message' => $request->input()], 200);
        // Dekripsi token hasil dari hidden form lalu dapatkan data KelasMapel
        $rekomendasiId = decrypt($request->rekomendasiId);

        try {
            $isHidden = 1;

            if ($request->opened) {
                $isHidden = 0;
            }
            $data = [
                'name' => $request->name,
                'content' => $request->content,
                'isHidden' => $isHidden,
            ];
            // Simpan data Rekomendasi ke database
            Rekomendasi::where('id', $rekomendasiId)->update($data);
            // Commit transaksi database
            DB::commit();

            // Berikan respons sukses jika semuanya berjalan lancar
            return response()->json(['message' => 'Rekomendasi berhasil dibuat'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200);
        }
    }

    /**
     * Menghapus Rekomendasi.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyRekomendasi(Request $request)
    {

        // Dapatkan Id Rekomendasi dari Inputan Form request
        $rekomendasiId = $request->hapusId;

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $request->kelasMapelId) {
                $dest = '../public_html/file/Rekomendasi'; // Destinasi tempat pengguna akan disimpan
                $files = RekomendasiFile::where('rekomendasi_id', $rekomendasiId)->get();
                foreach ($files as $key) {
                    if (file_exists(public_path($dest . '/' . $key->file))) {
                        unlink(public_path($dest . '/' . $key->file));
                    }
                }
                Rekomendasi::where('id', $rekomendasiId)->delete();
                RekomendasiFile::where('rekomendasi_id', $rekomendasiId)->delete();

                return redirect()->back()->with('success', 'Rekomendasi Berhasil dihapus');
            }
        }
        abort(404);
    }

    /**
     * Upload file Rekomendasi.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadFileRekomendasi(Request $request)
    {
        // Dapatkan Id Rekomendasi dari Inputan Form request
        // Validasi file yang diunggah
        $request->validate([
            'file' => 'required|file|max:2048', // Batasan ukuran maksimum adalah 2 MB (ganti sesuai kebutuhan Anda)
        ]);

        // return response()->json(['message' => $request->input()]);
        if ($request->action == 'tambah') {
            $latestRekomendasi = Rekomendasi::latest()->first();

            // Proses unggahan file di sini
            $file = $request->file('file');
            $fileName = 'F' . mt_rand(1, 999) . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/file/Rekomendasi'), $fileName); // Simpan file di direktori 'storage/app/uploads'

            RekomendasiFile::create([
                'rekomendasi_id' => $latestRekomendasi['id'],
                'file' => $fileName,
            ]);

            return response()->json(['message' => 'File berhasil diunggah.']); // Respon sukses
        } elseif ($request->action == 'edit') {
            // Proses unggahan file di sini
            $file = $request->file('file');
            $fileName = 'F' . mt_rand(1, 999) . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/file/Rekomendasi'), $fileName); // Simpan file di direktori 'storage/app/uploads'

            RekomendasiFile::create([
                'rekomendasi_id' => $request->idRekomendasi,
                'file' => $fileName,
            ]);

            return response()->json(['message' => 'File berhasil diunggah.']); // Respon sukses
        }

        return response()->json(['message' => 'File Error.']);
    }

    /**
     * Delete file Rekomendasi.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyFileRekomendasi(Request $request)
    {
        $idRekomendasi = $request->idRekomendasi;
        $fileName = $request->fileName;

        $dest = 'file/Rekomendasi'; // Destinasi tempat pengguna akan disimpan

        if (file_exists(public_path($dest . '/' . $fileName))) {
            unlink(public_path($dest . '/' . $fileName));
        }

        RekomendasiFile::where('rekomendasi_id', $idRekomendasi)->where('file', $fileName)->delete();

        return redirect()->back()->with('success', 'File Deleted');
    }

    /**
     * Delete file Rekomendasi.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectBack(Request $request)
    {
        $mapelId = request('amp;mapelId');
        $message = request('amp;message');

        return redirect(route('viewKelasMapel', ['mapel' => $mapelId, 'token' => encrypt($request->kelasId), 'mapel_id' => $mapelId]))->with('success', 'Data Berhasil di ' . $message);
    }
}
