<?php

namespace App\Http\Controllers; // Mendefinisikan namespace untuk controller

use Exception; // Mengimpor kelas Exception
use App\Models\Mapel; // Mengimpor model Mapel
use App\Models\KelasMapel; // Mengimpor model KelasMapel
use App\Exports\MapelExport; // Mengimpor kelas MapelExport
use App\Imports\MapelImport; // Mengimpor kelas MapelImport
use App\Models\EditorAccess; // Mengimpor model EditorAccess
use Illuminate\Http\Request; // Mengimpor kelas Request
use Maatwebsite\Excel\Facades\Excel; // Mengimpor kelas Excel dari Maatwebsite
use Illuminate\Support\Facades\Storage; // Mengimpor kelas Storage dari Illuminate

class MapelController extends Controller // Mendefinisikan kelas MapelController yang merupakan turunan dari Controller
{
    /**
     * Menampilkan halaman daftar mapel.
     *
     * @return \Illuminate\View\View
     */
    public function viewMapel() // Fungsi untuk menampilkan halaman daftar mapel
    {
        // Ambil peran pengguna
        $roles = DashboardController::getRolesName(); // Memanggil fungsi getRolesName dari DashboardController

        // Tampilkan halaman daftar mapel dengan data mapel yang dipaginasi
        return view('menu.admin.controlMapel.viewMapel', ['title' => 'Data Mapel', 'roles' => $roles, 'mapel' => Mapel::paginate(15)]);
    }

    /**
     * Mencari mapel berdasarkan kriteria tertentu.
     *
     * @return \Illuminate\View\View
     */
    public function searchMapel(Request $request) // Fungsi untuk mencari mapel berdasarkan kriteria tertentu
    {
        $search = $request->input('search'); // Mengambil inputan 'search' dari request
        $mapel = Mapel::where('name', 'like', '%' . $search . '%')->paginate(15); // Mencari mapel berdasarkan nama dengan pola tertentu

        return view('menu.admin.controlMapel.partials.mapelTable', compact('mapel'))->render(); // Menampilkan hasil pencarian mapel dalam bentuk tabel
    }

    /**
     * Menambahkan akses editor untuk mapel tertentu.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tambahEditorAccess(Request $request) // Fungsi untuk menambahkan akses editor untuk mapel tertentu
    {
        $kelasMapel = KelasMapel::where('kelas_id', $request->kelasId)->where('mapel_id', $request->mapelId)->first(); // Mengambil data kelas-mapel berdasarkan kelas_id dan mapel_id

        $temp = [
            'user_id' => $request->userId,
            'kelas_mapel_id' => $kelasMapel['id'],
        ];

        EditorAccess::create($temp); // Membuat data akses editor baru

        return response()->json(['response' => 'Added']); // Memberikan respons JSON 'Added'
    }

    /**
     * Menghapus akses editor untuk mapel tertentu.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEditorAccess(Request $request) // Fungsi untuk menghapus akses editor untuk mapel tertentu
    {
        $kelasMapel = KelasMapel::where('kelas_id', $request->kelasId)->where('mapel_id', $request->mapelId)->first(); // Mengambil data kelas-mapel berdasarkan kelas_id dan mapel_id

        if ($kelasMapel) {
            $kelasMapelId = $kelasMapel->id;

            EditorAccess::where('kelas_mapel_id', $kelasMapelId)->delete(); // Menghapus akses editor berdasarkan kelas_mapel_id

            return response()->json(['response' => 'Deleted']); // Memberikan respons JSON 'Deleted'
        } else {
            return response()->json(['response' => 'Data tidak ditemukan'], 404); // Memberikan respons JSON 'Data tidak ditemukan' dengan status 404
        }
    }

    /**
     * Menampilkan halaman tambah mapel.
     *
     * @return \Illuminate\View\View
     */
    public function viewTambahMapel() // Fungsi untuk menampilkan halaman tambah mapel
    {
        $roles = DashboardController::getRolesName(); // Memanggil fungsi getRolesName dari DashboardController

        return view('menu.admin.controlMapel.viewTambahMapel', ['title' => 'Tambah Mapel', 'roles' => $roles]); // Menampilkan halaman tambah mapel
    }

    /**
     * Menampilkan halaman update mapel.
     *
     * @return \Illuminate\View\View
     */
    public function viewUpdateMapel(Mapel $mapel) // Fungsi untuk menampilkan halaman update mapel
    {
        $roles = DashboardController::getRolesName(); // Memanggil fungsi getRolesName dari DashboardController

        return view('menu.admin.controlMapel.updateMapel', ['title' => 'Update Mapel', 'roles' => $roles, 'mapel' => $mapel]); // Menampilkan halaman update mapel
    }

    /**
     * Memeriksa apakah mapel sudah terhubung ke kelas tertentu.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cekKelasMapel(Request $request) // Fungsi untuk memeriksa apakah mapel sudah terhubung ke kelas tertentu
    {
        $response = KelasMapel::where('kelas_id', $request->kelasId)->where('mapel_id', $request->mapelId)->first(); // Mencari data kelas-mapel berdasarkan kelas_id dan mapel_id

        if (count($response->EditorAccess) > 0) {
            return response()->json(['response' => '1']); // Pesan jika memiliki akses Editor
        } else {
            return response()->json(['response' => '0']); // Pesan jika tidak memiliki akses Editor
        }
    }

    /**
     * Validasi data mapel sebelum penyimpanan.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validateNamaMapel(Request $request) // Fungsi untuk validasi data mapel sebelum penyimpanan
    {
        $request->validate([
            'name' => 'required|unique:mapels',
        ]);

        $desk = $request->deskripsi;

        if ($request->deskripsi == null) {
            $desk = '-';
        }

        $data = [
            'name' => $request->name,
            'deskripsi' => $desk,
        ];

        Mapel::create($data); // Membuat data mapel baru

        // Mencari id Mapel terakhir
        $mapelId = Mapel::latest()->first();
        $mapelId = $mapelId['id'];

        $data = [
            'prompt' => 'diTambahkan!',
            'action' => 'Tambah',
            'id' => $mapelId,
        ];
        session(['data' => $data]);

        return redirect(route('dataMapelSuccess')); // Redirect ke halaman sukses data mapel ditambahkan
    }

    /**
     * Memperbarui data mapel.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMapel(Request $request) // Fungsi untuk memperbarui data mapel
    {
        $request->validate([
            'nama' => 'required',
        ]);

        $desk = $request->deskripsi;

        if ($request->deskripsi == null) {
            $desk = '-';
        }

        $data = [
            'name' => $request->nama,
            'deskripsi' => $desk,
        ];

        Mapel::where('id', $request->id)->update($data); // Memperbarui data mapel berdasarkan id

        return redirect()->back()->with('success', 'Update berhasil!'); // Redirect kembali dengan pesan sukses
    }

    /**
     * Menambah atau mengubah akses editor untuk mapel tertentu.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addChangeEditorAccess(Request $request) // Fungsi untuk menambah atau mengubah akses editor untuk mapel tertentu
    {

        $kelasMapelId = KelasMapel::where('kelas_id', $request->kelasId)->where('mapel_id', $request->mapelId)->first();
        $kelasMapelId = $kelasMapelId['id'];

        $editorAccess = EditorAccess::where('kelas_mapel_id', $kelasMapelId)->get();
        // return response()->json(['success' => $editorAccess]);

        if ($request->pengajarId == 'delete') {
            EditorAccess::where('kelas_mapel_id', $kelasMapelId)->delete();

            return response()->json(['success' => 'deleted']);
        }

        try {
            if (count($editorAccess) > 0) {
                $data = ['user_id' => $request->pengajarId];
                EditorAccess::where('kelas_mapel_id', $kelasMapelId)->update($data);

                return response()->json(['success' => 1]);
            } else {
                EditorAccess::create(['user_id' => $request->pengajarId, 'kelas_mapel_id' => $kelasMapelId]);

                return response()->json(['success' => 0]);
            }
        } catch (Exception $e) {
            return response()->json(['success' => $e]);
        }
    }

    /**
     * Menangani penambahan gambar untuk mapel.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mapelTambahGambar(Request $request) // Fungsi untuk menangani penambahan gambar untuk mapel
    {
        $request->validate([
            'file' => 'file|image|max:4000',
        ]);
    
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $newImageName = 'UIMG' . date('YmdHis') . uniqid() . '.jpg'; // Nama gambar baru
    
            // Simpan file ke dalam folder public/img-upload
            $filePath = $file->move(storage_path('app/public/file/img-upload'), $newImageName);
    
            if (!$filePath) {
                return response()->json(['status' => 0, 'msg' => 'Upload gagal']);
            } else {
                // Hapus gambar lama jika ada
                $mapelInfo = Mapel::where('id', $request->id)->first();
                $mapelPhoto = $mapelInfo->gambar;
    
                if ($mapelPhoto != null && file_exists(storage_path('app/public/file/img-upload/' . $mapelPhoto))) {
                    // Hapus gambar lama dari penyimpanan
                    unlink(storage_path('app/public/file/img-upload/' . $mapelPhoto));
                }
    
                // Perbarui gambar
                $mapelInfo->update(['gambar' => $newImageName]);
    
                return response()->json(['status' => 1, 'msg' => 'Upload berhasil', 'name' => $newImageName]);
            }
        }
    
        return response()->json(['status' => 0, 'msg' => 'Tidak ada file yang diunggah']);
    }
    

    /**
     * Mencari kelas-mapel yang terkait dengan kelas tertentu.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchKelasMapel(Request $request) // Fungsi untuk mencari kelas-mapel yang terkait dengan kelas tertentu
    {

        $kelasMapel = KelasMapel::where('kelas_id', $request->kelasId)->get();
        $enrolledMapel = []; // Inisialisasi array untuk menyimpan data mapel yang diambil

        foreach ($kelasMapel as $key) {
            $mapel = Mapel::where('id', $key->mapel_id)->first();
            $pengajarExist = count($key->EditorAccess);
            $mapel['exist'] = $pengajarExist;

            if ($mapel) {
                $enrolledMapel[] = $mapel; // Tambahkan data mapel ke dalam array
            }
        }
        // dd($enrolledMapel);

        return response()->json($enrolledMapel);
    }

    /**
     * Menampilkan halaman sukses data mapel ditambahkan.
     *
     * @return \Illuminate\View\View
     */
    public function dataMapelSuccess() // Fungsi untuk menampilkan halaman sukses data mapel ditambahkan
    {
        if (session('data') != null) {
            $data = session('data');
            session()->forget('data');
            $roles = DashboardController::getRolesName();

            return view('menu.admin.controlMapel.dataSukses', ['title' => 'Sukses', 'roles' => $roles, 'data' => $data]);
        } else {
            abort(404);
        }
    }

    /**
     * Menghapus data mapel beserta kelas-mapel dan akses editor yang terkait.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyMapel(Request $request) // Fungsi untuk menghapus data mapel beserta kelas-mapel dan akses editor yang terkait
    {
        Mapel::destroy($request->idHapus);
        KelasMapel::where('mapel_id', $request->idHapus)->delete();
        EditorAccess::where('kelas_mapel_id', $request->idHapus)->delete();

        return redirect()->back()->with('delete-success', 'Berhasil menghapus Mapel!');
    }

    // Mapel

    /**
     * Mengunduh contoh data mapel dalam format Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function contohMapel() // Fungsi untuk mengunduh contoh data mapel dalam format Excel
    {
        // File PDF disimpan di dalam project/public/download/info.pdf
        $file = public_path() . '/examples/contoh-data-mapel.xls';

        return response()->download($file, 'contoh-mapel.xls');
    }

    /**
     * Mengunduh data kelas dalam format Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export() // Fungsi untuk mengunduh data kelas dalam format Excel
    {
        return Excel::download(new MapelExport, 'export-mapel.xls');
    }

    /**
     * Mengimpor data kelas dari file Excel.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request) // Fungsi untuk mengimpor data kelas dari file Excel
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        session()->forget('imported_ids', []);

        try {
            Excel::import(new MapelImport, $request->file('file'));

            $ids = session()->get('imported_ids');
            Mapel::whereNotIn('id', $ids)->delete();

            $editorId = KelasMapel::whereNotIn('mapel_id', $ids)->get('id');

            KelasMapel::whereNotIn('mapel_id', $ids)->delete();

            $editor = EditorAccess::whereIn('kelas_mapel_id', $editorId)->get();

            if (count($editor) > 0) {
                EditorAccess::whereIn('kelas_mapel_id', $editorId)->delete();
            }

            return redirect()->route('viewMapel')->with('import-success', 'Data Kelas berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->route('viewMapel')->with('import-error', 'Error: ' . $e);
        }
    }
}
