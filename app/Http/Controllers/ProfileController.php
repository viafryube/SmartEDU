<?php

namespace App\Http\Controllers; // Mendefinisikan namespace untuk controller

use App\Models\User; // Mengimpor model User
use App\Models\Kelas; // Mengimpor model Kelas
use App\Models\Mapel; // Mengimpor model Mapel
use App\Models\Contact; // Mengimpor model Contact
use App\Models\KelasMapel; // Mengimpor model KelasMapel
use App\Models\EditorAccess; // Mengimpor model EditorAccess
use Illuminate\Http\Request; // Mengimpor kelas Request
use Illuminate\Support\Facades\Crypt; // Mengimpor kelas Crypt dari Illuminate\Support\Facades
use Illuminate\Support\Facades\Storage; // Mengimpor kelas Storage dari Illuminate\Support\Facades

class ProfileController extends Controller // Mendefinisikan kelas ProfileController yang merupakan turunan dari Controller
{
    /**
     * Menampilkan profil pengajar berdasarkan token.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewProfilePengajar($token) // Fungsi untuk menampilkan profil pengajar berdasarkan token
    {
        try {
            $id = Crypt::decrypt($token); // Mendekripsi token menjadi ID
            $roles = DashboardController::getRolesName(); // Mendapatkan peran pengguna
            $profile = User::findOrFail($id); // Mendapatkan data pengguna berdasarkan ID
            $editorAccess = EditorAccess::where('user_id', $id)->get(); // Mendapatkan akses editor berdasarkan ID pengguna

            $mapelKelas = []; // Inisialisasi array mapelKelas

            foreach ($editorAccess as $key) { // Looping melalui akses editor
                $kelasMapel = KelasMapel::where('id', $key->kelas_mapel_id)->first(); // Mendapatkan data kelasMapel berdasarkan ID

                if ($kelasMapel) { // Pemeriksaan jika kelasMapel ada
                    $mapelID = $kelasMapel->mapel_id; // Mendapatkan ID mapel
                    $kelasID = $kelasMapel->kelas_id; // Mendapatkan ID kelas

                    // Pemeriksaan mapel
                    $mapelKey = array_search($mapelID, array_column($mapelKelas, 'mapel_id')); // Mencari key mapel dalam array mapelKelas

                    if ($mapelKey !== false) { // Pemeriksaan jika key mapel ditemukan
                        // Tambahkan ke Array
                        $mapelKelas[$mapelKey]['kelas'][] = Kelas::where('id', $kelasID)->first(); // Menambahkan data kelas ke dalam array
                    } else {
                        // Temukan Mapel
                        $mapelKelas[] = [ // Menambahkan data mapel baru ke dalam array
                            'mapel_id' => $mapelID,
                            'mapel' => Mapel::where('id', $mapelID)->first(),
                            'kelas' => [Kelas::where('id', $kelasID)->first()],
                        ];
                    }
                }
            }

            $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang di-assign

            return view('menu.profile.profilePengajar', ['assignedKelas' => $assignedKelas, 'user' => $profile, 'mapelKelas' => $mapelKelas,  'roles' => $roles, 'title' => 'Profil']); // Menampilkan view profil pengajar dengan data yang diperlukan
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani exception DecryptException
            abort(404); // Menghentikan proses dan menampilkan error 404
        }
    }

    /**
     * Menampilkan profil siswa berdasarkan token.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewProfileSiswa($token) // Fungsi untuk menampilkan profil siswa berdasarkan token
    {
        try {
            $id = Crypt::decrypt($token); // Mendekripsi token menjadi ID

            $roles = DashboardController::getRolesName(); // Mendapatkan peran pengguna
            $profile = User::findOrFail($id); // Mendapatkan data pengguna berdasarkan ID

            $kelas = Kelas::where('id', $profile->kelas_id)->first(); // Mendapatkan data kelas berdasarkan ID kelas pengguna

            $kelasMapel = KelasMapel::where('kelas_id', $kelas['id'])->get(); // Mendapatkan data kelasMapel berdasarkan ID kelas
            $mapelCollection = []; // Inisialisasi array mapelCollection

            foreach ($kelasMapel as $key) { // Looping melalui kelasMapel
                $mapel = Mapel::where('id', $key->mapel_id)->first(); // Mendapatkan data mapel berdasarkan ID

                $editorAccess = EditorAccess::where('kelas_mapel_id', $key->id)->first(); // Mendapatkan akses editor berdasarkan ID kelasMapel

                if ($editorAccess) { // Pemeriksaan jika akses editor ada
                    $editorAccess = $editorAccess['user_id']; // Mendapatkan ID pengguna akses editor
                    $pengajar = User::where('id', $editorAccess)->first(['id', 'name']); // Mendapatkan data pengajar berdasarkan ID
                    $pengajarNama = $pengajar['name']; // Mendapatkan nama pengajar
                    $pengajarId = $pengajar['id']; // Mendapatkan ID pengajar
                } else {
                    $pengajarNama = '-'; // Jika akses editor tidak ada, beri tanda '-'
                    $pengajarId = null; // Set ID pengajar menjadi null
                }

                $mapelCollection[] = [ // Menambahkan data mapel ke dalam array mapelCollection
                    'mapel_name' => $mapel['name'],
                    'mapel_id' => $mapel['id'],
                    'deskripsi' => $mapel['deskripsi'],
                    'gambar' => $mapel['gambar'],
                    'pengajar_id' => $pengajarId,
                    'pengajar_name' => $pengajarNama,
                ];
            }

            $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang di-assign

            return view('menu.profile.profileSiswa', ['assignedKelas' => $assignedKelas, 'user' => $profile, 'kelas' => $kelas, 'mapelKelas' => $mapelCollection, 'roles' => $roles, 'title' => 'Profil']); // Menampilkan view profil siswa dengan data yang diperlukan
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani exception DecryptException
            abort(404); // Menghentikan proses dan menampilkan error 404
        }
    }

    /**
     * Mengelola pengunggahan gambar profil pengguna.
     *
     * @return \Illuminate\Http\JsonResponse
     */


     public function cropImageUser(Request $request) // Fungsi untuk mengelola pengunggahan gambar profil pengguna
     {
         $request->validate([ // Validasi request
             'file' => 'file|image|max:4000', // Validasi file yang diunggah
         ]);
     
         if ($request->hasFile('file')) { // Pemeriksaan jika terdapat file yang diunggah
             $file = $request->file('file'); // Mendapatkan file yang diunggah
             $newImageName = 'UIMG' . date('YmdHis') . uniqid() . '.jpg'; // Generate nama baru
     
             // Simpan file ke dalam folder public/img-upload
             $path = $file->move(storage_path('app/public/file/img-upload'), $newImageName); // Memindahkan file ke direktori yang ditentukan
     
             if (!$path) { // Pemeriksaan jika penyimpanan gagal
                 return response()->json(['status' => 0, 'msg' => 'Upload Gagal']); // Respon gagal
             }
     
             // Hapus file gambar lama dari penyimpanan
             $userInfo = User::find($request->id); // Mendapatkan data pengguna berdasarkan ID
             $userPhoto = $userInfo->gambar; // Mendapatkan nama gambar pengguna
     
             if ($userPhoto != null && file_exists(storage_path('app/public/file/img-upload/' . $userPhoto))) { // Pemeriksaan jika gambar lama ada
                 unlink(storage_path('app/public/file/img-upload/' . $userPhoto)); // Menghapus gambar lama
             }
     
             // Perbarui gambar
             $userInfo->update(['gambar' => $newImageName]); // Memperbarui nama gambar pengguna
     
             return response()->json(['status' => 1, 'msg' => 'Upload berhasil', 'name' => $newImageName]); // Respon sukses
         }
     
         return response()->json(['status' => 0, 'msg' => 'Tidak ada file yang diunggah']); // Respon jika tidak ada file yang diunggah
     }
     


    /**
     * Menampilkan profil pengguna sendiri.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function myProfile($token) // Fungsi untuk menampilkan profil pengguna sendiri
    {
        try {
            $id = Crypt::decrypt($token); // Mendekripsi token menjadi ID

            $roles = DashboardController::getRolesName(); // Mendapatkan peran pengguna
            $profile = User::findOrFail($id); // Mendapatkan data pengguna berdasarkan ID

            $kelas = Kelas::where('id', $profile->kelas_id)->first(); // Mendapatkan data kelas berdasarkan ID kelas pengguna

            $kelasMapel = KelasMapel::where('kelas_id', $kelas['id'])->get(); // Mendapatkan data kelasMapel berdasarkan ID kelas
            $mapelCollection = []; // Inisialisasi array mapelCollection

            foreach ($kelasMapel as $key) { // Looping melalui kelasMapel
                $mapel = Mapel::where('id', $key->mapel_id)->first(); // Mendapatkan data mapel berdasarkan ID

                $editorAccess = EditorAccess::where('kelas_mapel_id', $key->id)->first(); // Mendapatkan akses editor berdasarkan ID kelasMapel

                if ($editorAccess) { // Pemeriksaan jika akses editor ada
                    $editorAccess = $editorAccess['user_id']; // Mendapatkan ID pengguna akses editor
                    $pengajar = User::where('id', $editorAccess)->first(['id', 'name']); // Mendapatkan data pengajar berdasarkan ID
                    $pengajarNama = $pengajar['name']; // Mendapatkan nama pengajar
                    $pengajarId = $pengajar['id']; // Mendapatkan ID pengajar
                } else {
                    $pengajarNama = '-'; // Jika akses editor tidak ada, beri tanda '-'
                    $pengajarId = null; // Set ID pengajar menjadi null
                }

                $mapelCollection[] = [ // Menambahkan data mapel ke dalam array mapelCollection
                    'mapel_name' => $mapel['name'],
                    'mapel_id' => $mapel['id'],
                    'deskripsi' => $mapel['deskripsi'],
                    'gambar' => $mapel['gambar'],
                    'pengajar_id' => $pengajarId,
                    'pengajar_name' => $pengajarNama,
                ];
            }

            $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang di-assign

            return view('menu.profile.profileSiswa', ['assignedKelas' => $assignedKelas, 'user' => $profile, 'kelas' => $kelas['name'], 'mapelKelas' => $mapelCollection, 'roles' => $roles, 'title' => 'Profil']); // Menampilkan view profil siswa dengan data yang diperlukan
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani exception DecryptException
            abort(404); // Menghentikan proses dan menampilkan error 404
        }
    }

    /**
     * Menampilkan halaman pengaturan profil pengguna.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function viewProfileSetting($token) // Fungsi untuk menampilkan halaman pengaturan profil pengguna
    {
        try {
            $id = Crypt::decrypt($token); // Mendekripsi token menjadi ID
            $roles = DashboardController::getRolesName(); // Mendapatkan peran pengguna

            if ($id == Auth()->User()->id) { // Pemeriksaan jika ID sama dengan ID pengguna yang sedang login
                $user = User::where('id', $id)->first(); // Mendapatkan data pengguna berdasarkan ID
                $contact = Contact::where('user_id', $id)->first(); // Mendapatkan data kontak berdasarkan ID pengguna
                $kelas = Kelas::where('id', Auth()->User()->kelas_id)->first(); // Mendapatkan data kelas berdasarkan ID kelas pengguna
                $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang di-assign

                return view('menu.profile.setting.settingUser', ['assignedKelas' => $assignedKelas, 'kelas' => $kelas, 'user' => $user, 'contact' => $contact, 'title' => 'Profil Setting', 'roles' => $roles]); // Menampilkan view pengaturan profil pengguna dengan data yang diperlukan
            } else {
                abort(404); // Menghentikan proses dan menampilkan error 404
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani exception DecryptException
            abort(404); // Menghentikan proses dan menampilkan error 404
        }
    }
}
