<?php

namespace App\Http\Controllers;

use App\Models\EditorAccess;
use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\Mapel;
use App\Models\Pengumuman;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class : PengumumanController
 *
 * Class ini berisi berbagai fungsi yang berkaitan dengan manipulasi data-data pengumuman, terutama terkait dengan model.

 */
class PengumumanController extends Controller
{
    /**
     * Menampilkan halaman Tambah Pengumuman.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewCreatePengumuman($token, Request $request)
    {
        // id = Kelas Id
        $id = decrypt($token);
        $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $id)->first();

        $preparedIdPengumuman = count(Pengumuman::get());
        $preparedIdPengumuman = $preparedIdPengumuman + 1;
        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $kelasMapel['id']) {
                $roles = DashboardController::getRolesName();
                $mapel = Mapel::where('id', $request->mapelId)->first();

                $assignedKelas = DashboardController::getAssignedClass();

                return view('menu.pengajar.pengumuman.viewTambahPengumuman', ['assignedKelas' => $assignedKelas, 'title' => 'Tambah Pengumuman', 'roles' => $roles, 'kelasId' => $id, 'mapel' => $mapel, 'preparedIdPengumuman' => $preparedIdPengumuman]);
            }
        }
        abort(404);
    }

    /**
     * Menampilkan halaman Update Pengumuman.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewUpdatePengumuman($token, Request $request)
    {
        // token = Pengumuman Id
        $id = decrypt($token);
        $pengumuman = Pengumuman::where('id', $id)->first();  // Dapatkan Pengumuman

        // Dapatkan kelas mapel untuk dibandingkan dengan pengumuman
        $kelasMapel = KelasMapel::where('id', $pengumuman->kelas_mapel_id)->first();

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $kelasMapel['id']) {
                $roles = DashboardController::getRolesName();
                $mapel = Mapel::where('id', $request->mapelId)->first();

                $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id');

                $assignedKelas = DashboardController::getAssignedClass();

                return view('menu.pengajar.pengumuman.viewUpdatePengumuman', ['assignedKelas' => $assignedKelas, 'title' => 'Update Pengumuman', 'pengumuman' => $pengumuman, 'roles' => $roles, 'kelasId' => $kelas['id'], 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]);
            }
        }
        abort(404);
    }

    /**
     * Menampilkan halaman Pengumuman.
     *
     * @return \Illuminate\View\View
     */
    public function viewPengumuman(Request $request)
    {
        // pengumuman id
        $id = decrypt($request->token);
        //kelasMapel id
        $idx = decrypt($request->kelasMapelId);

        $pengumuman = Pengumuman::where('id', $id)->first();

        $roles = DashboardController::getRolesName();
        $kelasMapel = KelasMapel::where('id', $pengumuman->kelas_mapel_id)->first();

        // Dapatkan Pengajar
        $editorAccess = EditorAccess::where('kelas_mapel_id', $kelasMapel['id'])->first();
        $editorData = User::where('id', $editorAccess['user_id'])->where('roles_id', 2)->first();

        $mapel = Mapel::where('id', $request->mapelId)->first();
        $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id');

        $pengumumanAll = Pengumuman::where('kelas_mapel_id', $idx)->get();

        $assignedKelas = DashboardController::getAssignedClass();

        return view('menu.pengajar.pengumuman.viewPengumuman', ['assignedKelas' => $assignedKelas, 'editor' => $editorData, 'pengumuman' => $pengumuman, 'kelas' => $kelas, 'title' => $pengumuman->name, 'roles' => $roles, 'pengumumanAll' => $pengumumanAll, 'mapel' => $mapel, 'kelasMapel' => $kelasMapel]);
    }

    /**
     * Membuat Pengumuman baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createPengumuman(Request $request)
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

            // Simpan data Pengumuman ke database
            Pengumuman::create($temp);

            // Commit transaksi database
            DB::commit();

            // Berikan respons sukses jika semuanya berjalan lancar
            return response()->json(['message' => 'Pengumuman berhasil dibuat'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200);
        }
    }

    /**
     * Mengupdate Pengumuman.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePengumuman(Request $request)
    {
        // Lakukan validasi untuk inputan form
        $request->validate([
            'name' => 'required',
            'content' => 'required',
        ]);
        // return response()->json(['message' => $request->input()], 200);
        // Dekripsi token hasil dari hidden form lalu dapatkan data KelasMapel
        $pengumumanId = decrypt($request->pengumumanId);

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
            // Simpan data Pengumuman ke database
            Pengumuman::where('id', $pengumumanId)->update($data);
            // Commit transaksi database
            DB::commit();

            // Berikan respons sukses jika semuanya berjalan lancar
            return response()->json(['message' => 'Pengumuman berhasil dibuat'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200);
        }
    }

    /**
     * Menghapus Pengumuman.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyPengumuman(Request $request)
    {

        // Dapatkan Id Pengumuman dari Inputan Form request
        $pengumumanId = $request->hapusId;

        // Logika untuk memeriksa apakah pengguna yang sudah login memiliki akses editor
        foreach (Auth()->User()->EditorAccess as $key) {
            if ($key->kelas_mapel_id == $request->kelasMapelId) {
                Pengumuman::where('id', $pengumumanId)->delete();

                return redirect()->back()->with('success', 'Pengumuman Berhasil dihapus');
            }
        }
        abort(404);
    }


    public function redirectBack(Request $request)
    {
        $mapelId = request('amp;mapelId');
        $message = request('amp;message');

        return redirect(route('viewKelasMapel', ['mapel' => $mapelId, 'token' => encrypt($request->kelasId), 'mapel_id' => $mapelId]))->with('success', 'Data Berhasil di ' . $message);
    }
}
