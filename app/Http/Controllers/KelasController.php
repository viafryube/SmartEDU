<?php

namespace App\Http\Controllers;

// Mengimpor berbagai kelas dan model yang diperlukan
use App\Exports\KelasExport;
use App\Imports\KelasImport;
use App\Models\DataSiswa;
use App\Models\EditorAccess;
use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KelasController extends Controller
{
    /**
     * Menampilkan halaman data kelas.
     *
     * @return \Illuminate\View\View
     */
    public function viewKelas()
    {
        // Mendapatkan peran pengguna
        $roles = DashboardController::getRolesName();

        // Mengembalikan tampilan halaman data kelas dengan data yang diperlukan
        return view('menu.admin.controlKelas.viewKelas', ['title' => 'Data Kelas', 'roles' => $roles, 'kelas' => Kelas::paginate(15), 'mapelCount' => count(Mapel::get())]);
    }

    /**
     * Mencari kelas berdasarkan nama.
     *
     * @return \Illuminate\View\View
     */
    public function searchKelas(Request $request)
    {
        // Mengambil kata kunci pencarian dari permintaan
        $search = $request->input('search');

        // Mencari kelas yang namanya sesuai dengan kata kunci pencarian
        $kelas = Kelas::where('name', 'like', '%' . $search . '%')->paginate(15);

        // Mengembalikan tampilan hasil pencarian kelas
        return view('menu.admin.controlKelas.partials.kelasTable', compact('kelas'))->render();
    }

    /**
     * Menampilkan halaman tambah kelas.
     *
     * @return \Illuminate\View\View
     */
    public function viewTambahKelas()
    {
        // Mendapatkan peran pengguna
        $roles = DashboardController::getRolesName();

        // Mengembalikan tampilan halaman tambah kelas dengan data yang diperlukan
        return view('menu.admin.controlKelas.tambahKelas', ['title' => 'Tambah Kelas', 'roles' => $roles, 'dataMapel' => Mapel::get()]);
    }

    /**
     * Validasi dan menyimpan nama kelas baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validateNamaKelas(Request $request)
    {
        // Validasi input nama kelas
        $request->validate([
            'name' => 'required|unique:kelas',
        ]);

        // Menyimpan data kelas baru
        $data = [
            'name' => $request->name,
        ];
        Kelas::create($data);

        // Mendapatkan kelas terbaru yang baru ditambahkan
        $latestKelas = Kelas::latest('id')->first();

        // Menyimpan data mapel yang terkait dengan kelas jika ada
        if ($request->mapels) {
            foreach ($request->mapels as $key) {
                $data = ['kelas_id' => $latestKelas['id'], 'mapel_id' => $key];
                KelasMapel::create($data);
            }
        }

        // Menyimpan pesan sukses dalam sesi
        $data = [
            'prompt' => 'ditambahkan!',
            'action' => 'Tambah',
        ];
        session(['data' => $data]);

        // Mengarahkan ke halaman sukses
        return redirect(route('dataKelasSuccess'));
    }

    /**
     * Menampilkan halaman sukses setelah menambahkan kelas.
     *
     * @return \Illuminate\View\View
     */
    public function dataKelasSuccess()
    {
        // Memeriksa apakah ada data dalam sesi
        if (session('data') != null) {
            $data = session('data');
            session()->forget('data');
            $roles = DashboardController::getRolesName();

            // Mengembalikan tampilan halaman sukses dengan data yang diperlukan
            return view('menu.admin.controlKelas.dataSukses', ['title' => 'Sukses', 'roles' => $roles, 'data' => $data]);
        } else {
            abort(404);
        }
    }

    /**
     * Menghapus kelas dan data terkait.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyKelas(Request $request)
    {
        // Mendapatkan data kelas_mapel yang terkait dengan kelas yang akan dihapus
        $kelasMapelId = KelasMapel::where('kelas_id', $request->idHapus)->get();

        // Menghapus akses editor yang terkait dengan kelas_mapel
        foreach ($kelasMapelId as $key) {
            EditorAccess::where('kelas_mapel_id', $key['id'])->delete();
        }

        // Menghapus data kelas_mapel dan kelas
        KelasMapel::where('kelas_id', $request->idHapus)->delete();
        Kelas::destroy($request->idHapus);

        // Mengarahkan kembali dengan pesan sukses
        return redirect()->back()->with('delete-success', 'Berhasil menghapus kelas!');
    }

    /**
     * Menampilkan halaman update kelas.
     *
     * @return \Illuminate\View\View
     */
    public function viewUpdateKelas(Kelas $kelas)
    {
        // Mendapatkan data kelas_mapel yang terkait dengan kelas
        $kelasMapel = KelasMapel::where('kelas_id', $kelas->id)->get();
        $enrolledMapel = [];

        // Mendapatkan data mapel yang terkait dengan kelas
        foreach ($kelasMapel as $key) {
            $mapel = Mapel::where('id', $key->mapel_id)->first();

            if ($mapel) {
                $enrolledMapel[] = $mapel;
            }
        }

        // Mendapatkan semua data mapel
        $mapel = Mapel::get();
        $roles = DashboardController::getRolesName();

        // Mengembalikan tampilan halaman update kelas dengan data yang diperlukan
        return view('menu.admin.controlKelas.updateKelas', ['title' => 'Update Kelas', 'roles' => $roles, 'kelas' => $kelas, 'dataMapel' => $mapel, 'kelasMapel' => $enrolledMapel]);
    }

    /**
     * Menampilkan halaman detail kelas.
     *
     * @return \Illuminate\View\View
     */
    public function viewDetailKelas(Request $request)
    {
        // Mendapatkan data kelas berdasarkan ID
        $kelas = Kelas::where('id', $request->kelasId)->first();
        $kelasMapel = KelasMapel::where('kelas_id', $request->kelasId)->get();
        $enrolledMapel = [];

        // Mendapatkan data mapel dan pengajar yang terkait dengan kelas
        foreach ($kelasMapel as $key) {
            $mapel = Mapel::where('id', $key->mapel_id)->first(['id', 'name']);
            $pengajarName = null;

            if (count($key->EditorAccess) > 0) {
                $pengajar = User::where('id', $key->EditorAccess[0]->user_id)->first();
                $pengajarName = $pengajar ? $pengajar->name : null;
                $pengajarId = $pengajar ? $pengajar->id : null;
            } else {
                $pengajarName = null;
                $pengajarId = null;
            }

            if ($mapel) {
                $enrolledMapel[] = [
                    'id' => $mapel->id,
                    'name' => $mapel->name,
                    'pengajarName' => $pengajarName,
                    'pengajarId' => $pengajarId,
                ];
            }
        }

        // Mendapatkan data pengajar
        $pengajar = User::where('roles_id', 2)->get();

        // Mengembalikan tampilan halaman detail kelas dengan data yang diperlukan
        return view('menu.admin.controlKelas.partials.mapelList', ['enrolledMapel' => $enrolledMapel, 'pengajar' => $pengajar, 'kelas' => $kelas])->render();
    }

    /**
     * Mengupdate kelas dan mapel terkait.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateKelas(Request $request)
    {
        // Mendapatkan ID kelas dan mapel yang harus tetap ada
        $id = $request->id;
        $mapelsToKeep = $request->mapels;

        // Mendapatkan data kelas_mapel yang terkait dengan kelas
        $idKelasNew = KelasMapel::where('kelas_id', $id)->get();
        $temp = [];

        // Menyimpan ID mapel dalam array sementara
        foreach ($idKelasNew as $key) {
            array_push($temp, $key->mapel_id);
        }

        // Menghitung perbedaan antara mapel yang harus tetap ada dan yang ada di database
        $diff = array_diff($mapelsToKeep, $temp);

        // Menambahkan data kelas_mapel baru jika ada perbedaan
        foreach ($diff as $key) {
            $data = [
                'kelas_id' => $id,
                'mapel_id' => $key,
            ];
            KelasMapel::create($data);
        }

        // Mendapatkan data kelas_mapel yang harus dihapus
        $idKelas = KelasMapel::where('kelas_id', $id)->whereNotIn('mapel_id', $mapelsToKeep)->get();

        // Menghapus data kelas_mapel yang tidak diperlukan
        KelasMapel::where('kelas_id', $id)
            ->whereNotIn('mapel_id', $mapelsToKeep)
            ->delete();

        // Menghapus data akses editor yang terkait dengan kelas_mapel yang dihapus
        foreach ($idKelas as $key) {
            EditorAccess::where('kelas_mapel_id', $key['id'])->delete();
        }

        // Mengupdate nama kelas jika ada perubahan
        if ($request->nama) {
            Kelas::where('id', $id)->update(['name' => $request->nama]);
        }

        // Mengarahkan kembali dengan pesan sukses
        return redirect()->back()->with('success', 'Update berhasil!');
    }

    // Kelas

    /**
     * Mengunduh data kelas dalam format Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // Mengunduh data kelas dalam format Excel
        return Excel::download(new KelasExport, 'export-kelas.xls');
    }

    /**
     * Mengimpor data kelas dari file Excel.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        // Validasi input file
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        session()->forget('imported_ids', []);

        try {
            // Mengimpor data kelas dari file Excel
            Excel::import(new KelasImport, $request->file('file'));

            // Mendapatkan ID kelas yang berhasil diimpor
            $ids = session()->get('imported_ids');
            Kelas::whereNotIn('id', $ids)->delete();

            // Mengupdate data siswa yang terkait dengan kelas yang diimpor
            DataSiswa::whereNotIn('kelas_id', $ids)->update(['kelas_id' => null]);

            // Menghapus data kelas_mapel yang tidak diperlukan
            $editorId = KelasMapel::whereNotIn('kelas_id', $ids)->get('id');
            KelasMapel::whereNotIn('kelas_id', $ids)->delete();

            // Menghapus data akses editor yang tidak diperlukan
            $editor = EditorAccess::whereIn('kelas_mapel_id', $editorId)->get();
            if (count($editor) > 0) {
                EditorAccess::whereIn('kelas_mapel_id', $editorId)->delete();
            }

            // Mengarahkan ke halaman view kelas dengan pesan sukses
            return redirect()->route('viewKelas')->with('import-success', 'Data Kelas berhasil diimpor.');
        } catch (\Exception $e) {
            // Mengarahkan ke halaman view kelas dengan pesan error
            return redirect()->route('viewKelas')->with('import-error', 'Error: ' . $e);
        }
    }

    /**
     * Mengunduh contoh data kelas dalam format Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function contohKelas()
    {
        // Mendapatkan path file contoh data kelas
        $file = public_path('/examples/contoh-data-kelas.xls');

        // Mengunduh file contoh data kelas
        return response()->download($file, 'contoh-kelas.xls');
    }
}
