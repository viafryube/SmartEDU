<?php

namespace App\Http\Controllers;

use App\Models\EditorAccess; // Mengimpor model EditorAccess
use App\Models\Kelas; // Mengimpor model Kelas
use App\Models\KelasMapel; // Mengimpor model KelasMapel
use App\Models\Mapel; // Mengimpor model Mapel
use App\Models\Diskusi; // Mengimpor model Diskusi
use App\Models\User; // Mengimpor model User
use Exception; // Mengimpor kelas Exception
use Illuminate\Http\Request; // Mengimpor Request dari Laravel
use Illuminate\Support\Facades\DB; // Mengimpor DB dari Laravel
use Livewire\Livewire; // Mengimpor Livewire

/**
 * Class : DiskusiController
 *
 * Class ini berisi berbagai fungsi yang berkaitan dengan manipulasi data-data diskusi, terutama terkait dengan model.
 */
class DiskusiController extends Controller
{
    /**
     * Menampilkan halaman Tambah Diskusi.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewCreateDiskusi($token, Request $request)
    {
        $id = decrypt($token); // Mendekripsi token untuk mendapatkan ID kelas
        $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $id)->first(); // Mencari kelasMapel berdasarkan mapelId dan kelasId

        $preparedIdDiskusi = count(Diskusi::get()); // Menghitung jumlah diskusi yang ada
        $preparedIdDiskusi = $preparedIdDiskusi + 1; // Menambah 1 untuk ID diskusi berikutnya
        foreach (Auth()->User()->EditorAccess as $key) { // Loop untuk memeriksa akses editor pengguna
            if ($key->kelas_mapel_id == $kelasMapel['id']) { // Memeriksa apakah pengguna memiliki akses ke kelasMapel
                $roles = DashboardController::getRolesName(); // Mendapatkan nama peran pengguna
                $mapel = Mapel::where('id', $request->mapelId)->first(); // Mencari mapel berdasarkan mapelId

                $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang ditugaskan

                return view('menu.pengajar.diskusi.viewTambahDiskusi', [
                    'assignedKelas' => $assignedKelas,
                    'title' => 'Tambah Diskusi',
                    'roles' => $roles,
                    'kelasId' => $id,
                    'mapel' => $mapel,
                    'preparedIdDiskusi' => $preparedIdDiskusi
                ]); // Menampilkan halaman tambah diskusi dengan data yang dikumpulkan
            }
        }
        abort(404); // Mengembalikan halaman 404 jika pengguna tidak memiliki akses
    }

    /**
     * Menampilkan halaman Update Diskusi.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewUpdateDiskusi($token, Request $request)
    {
        $id = decrypt($token); // Mendekripsi token untuk mendapatkan ID diskusi
        $diskusi = Diskusi::where('id', $id)->first(); // Mencari diskusi berdasarkan ID

        $kelasMapel = KelasMapel::where('id', $diskusi->kelas_mapel_id)->first(); // Mencari kelasMapel berdasarkan ID kelasMapel dalam diskusi

        foreach (Auth()->User()->EditorAccess as $key) { // Loop untuk memeriksa akses editor pengguna
            if ($key->kelas_mapel_id == $kelasMapel['id']) { // Memeriksa apakah pengguna memiliki akses ke kelasMapel
                $roles = DashboardController::getRolesName(); // Mendapatkan nama peran pengguna
                $mapel = Mapel::where('id', $request->mapelId)->first(); // Mencari mapel berdasarkan mapelId

                $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id'); // Mencari kelas berdasarkan kelasId dalam kelasMapel

                $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang ditugaskan

                return view('menu.pengajar.diskusi.viewUpdateDiskusi', [
                    'assignedKelas' => $assignedKelas,
                    'title' => 'Update Diskusi',
                    'diskusi' => $diskusi,
                    'roles' => $roles,
                    'kelasId' => $kelas['id'],
                    'mapel' => $mapel,
                    'kelasMapel' => $kelasMapel
                ]); // Menampilkan halaman update diskusi dengan data yang dikumpulkan
            }
        }
        abort(404); // Mengembalikan halaman 404 jika pengguna tidak memiliki akses
    }

    /**
     * Menampilkan halaman Diskusi.
     *
     * @return \Illuminate\View\View
     */
    public function viewDiskusi(Request $request)
    {
        $id = decrypt($request->token); // Mendekripsi token untuk mendapatkan ID diskusi
        $idx = decrypt($request->kelasMapelId); // Mendekripsi kelasMapelId

        $diskusi = Diskusi::where('id', $id)->first(); // Mencari diskusi berdasarkan ID

        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran pengguna
        $kelasMapel = KelasMapel::where('id', $diskusi->kelas_mapel_id)->first(); // Mencari kelasMapel berdasarkan ID kelasMapel dalam diskusi

        $editorAccess = EditorAccess::where('kelas_mapel_id', $kelasMapel['id'])->first(); // Mencari akses editor berdasarkan kelasMapelId
        $editorData = User::where('id', $editorAccess['user_id'])->where('roles_id', 2)->first(); // Mencari data pengguna yang memiliki akses editor

        $mapel = Mapel::where('id', $request->mapelId)->first(); // Mencari mapel berdasarkan mapelId
        $kelas = Kelas::where('id', $kelasMapel['kelas_id'])->first('id'); // Mencari kelas berdasarkan kelasId dalam kelasMapel

        $diskusiAll = Diskusi::where('kelas_mapel_id', $idx)->get(); // Mendapatkan semua diskusi berdasarkan kelasMapelId

        $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang ditugaskan

        return view('menu.pengajar.diskusi.viewDiskusi', [
            'assignedKelas' => $assignedKelas,
            'editor' => $editorData,
            'diskusi' => $diskusi,
            'kelas' => $kelas,
            'title' => $diskusi->name,
            'roles' => $roles,
            'diskusiAll' => $diskusiAll,
            'mapel' => $mapel,
            'kelasMapel' => $kelasMapel,
            'diskusiId' => $id
        ]); // Menampilkan halaman diskusi dengan data yang dikumpulkan
    }

    /**
     * Membuat Diskusi baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createDiskusi(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'content' => 'required',
        ]); // Melakukan validasi untuk inputan form

        try {
            $token = decrypt($request->kelasId); // Mendekripsi token untuk mendapatkan ID kelas
            $kelasMapel = KelasMapel::where('mapel_id', $request->mapelId)->where('kelas_id', $token)->first(); // Mencari kelasMapel berdasarkan mapelId dan kelasId

            $isHidden = 1; // Default status diskusi tersembunyi

            if ($request->opened) {
                $isHidden = 0; // Jika diskusi dibuka, ubah status tersembunyi menjadi 0
            }
            $temp = [
                'kelas_mapel_id' => $kelasMapel['id'],
                'name' => $request->name,
                'content' => $request->content,
                'isHidden' => $isHidden,
            ]; // Menyiapkan data untuk diskusi baru

            Diskusi::create($temp); // Menyimpan data diskusi ke database

            DB::commit(); // Melakukan commit transaksi database

            return response()->json(['message' => 'Diskusi berhasil dibuat'], 200); // Memberikan respons sukses
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200); // Memberikan respons error jika terjadi kesalahan
        }
    }

    /**
     * Mengupdate Diskusi.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDiskusi(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'content' => 'required',
        ]); // Melakukan validasi untuk inputan form

        $diskusiId = decrypt($request->diskusiId); // Mendekripsi token untuk mendapatkan ID diskusi

        try {
            $isHidden = 1; // Default status diskusi tersembunyi

            if ($request->opened) {
                $isHidden = 0; // Jika diskusi dibuka, ubah status tersembunyi menjadi 0
            }
            $data = [
                'name' => $request->name,
                'content' => $request->content,
                'isHidden' => $isHidden,
            ]; // Menyiapkan data untuk update diskusi

            Diskusi::where('id', $diskusiId)->update($data); // Mengupdate data diskusi di database

            DB::commit(); // Melakukan commit transaksi database

            return response()->json(['message' => 'Diskusi berhasil diupdate'], 200); // Memberikan respons sukses
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 200); // Memberikan respons error jika terjadi kesalahan
        }
    }

    /**
     * Menghapus Diskusi.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyDiskusi(Request $request)
    {
        $diskusiId = $request->hapusId; // Mendapatkan ID diskusi dari inputan form request

        foreach (Auth()->User()->EditorAccess as $key) { // Loop untuk memeriksa akses editor pengguna
            if ($key->kelas_mapel_id == $request->kelasMapelId) { // Memeriksa apakah pengguna memiliki akses ke kelasMapel
                Diskusi::where('id', $diskusiId)->delete(); // Menghapus diskusi berdasarkan ID

                return redirect()->back()->with('success', 'Diskusi Berhasil dihapus'); // Mengarahkan kembali dengan pesan sukses
            }
        }
        abort(404); // Mengembalikan halaman 404 jika pengguna tidak memiliki akses
    }

    public function redirectBack(Request $request)
    {
        $mapelId = request('amp;mapelId'); // Mendapatkan mapelId dari request
        $message = request('amp;message'); // Mendapatkan pesan dari request

        return redirect(route('viewKelasMapel', [
            'mapel' => $mapelId,
            'token' => encrypt($request->kelasId),
            'mapel_id' => $mapelId
        ]))->with('success', 'Data Berhasil di ' . $message); // Mengarahkan kembali dengan pesan sukses
    }
}
