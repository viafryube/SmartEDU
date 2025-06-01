<?php

namespace App\Http\Controllers; // Menentukan namespace untuk Controller

use App\Models\DataSiswa; // Mengimpor model DataSiswa
use App\Models\EditorAccess; // Mengimpor model EditorAccess
use App\Models\Kelas; // Mengimpor model Kelas
use App\Models\KelasMapel; // Mengimpor model KelasMapel
use App\Models\Mapel; // Mengimpor model Mapel
use App\Models\Materi; // Mengimpor model Materi
use App\Models\Role; // Mengimpor model Role
use App\Models\Tugas; // Mengimpor model Tugas
use App\Models\Ujian; // Mengimpor model Ujian
use App\Models\User; // Mengimpor model User
use Illuminate\Support\Facades\Crypt; // Mengimpor fasad Crypt untuk enkripsi/dekripsi

/**
 * Class : DashboardController
 *
 * Kelas ini mengelola berbagai fungsi yang berkaitan dengan pengguna dan dasbor,
 *

 */
class DashboardController extends Controller // Mendefinisikan kelas DashboardController yang merupakan turunan dari Controller
{
    /**
     * Menampilkan halaman dasbor. Pengguna akan diarahkan berdasarkan roles id mereka.
     *
     * @return \Illuminate\View\View
     */
    public function viewDashboard() // Mendefinisikan metode viewDashboard
    {
        // Mengumpulkan beberapa informasi tentang pengguna.
        $authRoles = $this->getAuthId(); // Mendapatkan ID peran pengguna yang sedang login
        $authRolesName = $this->getRolesName(); // Mendapatkan nama peran pengguna yang sedang login

        // Pengkondisian
        // Roles_id : 1 = Admin
        // Roles_id : 2 = Pengajar
        // Roles_id : 3 = Siswa
        if ($authRoles == 1) { // Jika pengguna adalah Admin (roles_id = 1)

            $data = [
                'totalSiswa' => count(DataSiswa::get()), // Menghitung total siswa
                'totalUserSiswa' => count(User::where('roles_id', 3)->get()), // Menghitung total pengguna siswa
                'totalPengajar' => count(User::where('roles_id', 2)->get()), // Menghitung total pengajar
                'totalKelas' => count(Kelas::get()), // Menghitung total kelas
                'totalMapel' => count(Mapel::get()), // Menghitung total mata pelajaran
                'totalMateri' => count(Materi::get()), // Menghitung total materi
                'totalTugas' => count(Tugas::get()), // Menghitung total tugas
                'totalUjian' => count(Ujian::get()), // Menghitung total ujian
            ];

            return view('menu/admin/dashboard/dashboard', ['materi' => Materi::all(), 'title' => 'Dashboard', 'roles' => $authRolesName, 'data' => $data]); // Menampilkan halaman dasbor admin dengan data yang dikumpulkan
        } elseif ($authRoles == 2) { // Jika pengguna adalah Pengajar (roles_id = 2)
            try {
                // Dapatkan ID Pengguna
                $id = Auth()->User()->id; // Mendapatkan ID pengguna yang sedang login

                // Kueri
                $roles = DashboardController::getRolesName(); // Mendapatkan nama peran pengguna
                $profile = User::findOrFail($id); // Mendapatkan profil pengguna berdasarkan ID
                $editorAccess = EditorAccess::where('user_id', $id)->get(); // Mendapatkan akses editor untuk pengguna

                // Inisialisasi Array Kosong
                $mapelKelas = []; // Inisialisasi array kosong untuk mata pelajaran kelas
                $totalSiswa = 0; // Inisialisasi total siswa
                $totalSiswaUnique = []; // Inisialisasi array kosong untuk ID siswa unik
                $kelasMapelId = []; // Inisialisasi array kosong untuk ID kelas mapel
                $kelasInfo = []; // Inisialisasi array kosong untuk informasi kelas
                // Membangun Data yang berkaitan dengan Pengguna dan apa yang mereka Ajar.
                // Sehingga akan muncul di Dasbor apa yang mereka ajar (Editor Access).
                foreach ($editorAccess as $key) { // Iterasi melalui akses editor
                    $kelasMapel = KelasMapel::where('id', $key->kelas_mapel_id)->first(); // Mendapatkan kelas mapel berdasarkan ID akses editor

                    if ($kelasMapel) { // Jika kelas mapel ditemukan
                        $mapelID = $kelasMapel->mapel_id; // Mendapatkan ID mata pelajaran
                        $kelasID = $kelasMapel->kelas_id; // Mendapatkan ID kelas

                        // Pemeriksa Mapel
                        $mapelKey = array_search($mapelID, array_column($mapelKelas, 'mapel_id')); // Mencari indeks mapel dalam array

                        if ($mapelKey !== false) { // Jika mapel ditemukan dalam array
                            // Tambahkan ke Array
                            $mapelKelas[$mapelKey]['kelas'][] = Kelas::where('id', $kelasID)->first(); // Tambahkan kelas ke array mapel
                        } else { // Jika mapel tidak ditemukan dalam array
                            // Temukan Mapel
                            $mapelKelas[] = [
                                'mapel_id' => $mapelID, // Tambahkan ID mapel ke array
                                'mapel' => Mapel::where('id', $mapelID)->first(), // Tambahkan data mapel ke array
                                'kelas' => [Kelas::where('id', $kelasID)->first()], // Tambahkan kelas ke array mapel
                            ];
                            array_push($kelasMapelId, $kelasMapel['id']); // Tambahkan ID kelas mapel ke array
                        }

                        // Count Siswa
                        $siswa = DataSiswa::where('kelas_id', $kelasID)->get(); // Mendapatkan data siswa berdasarkan ID kelas
                        $totalSiswa += count($siswa); // Menambahkan jumlah siswa ke total siswa

                        // Extract unique student IDs
                        $totalSiswaUnique = array_merge($totalSiswaUnique, $siswa->pluck('id')->toArray()); // Menggabungkan ID siswa unik ke array
                    }
                }

                // dd($kelasMapelId);
                $totalSiswaUnique = array_unique($totalSiswaUnique); // Menghilangkan duplikasi ID siswa
                $totalSiswaUnique = count($totalSiswaUnique); // Menghitung jumlah siswa unik

                $assignedKelas = $this->getAssignedClass(); // Mendapatkan kelas yang ditugaskan

                return view('menu/pengajar/dashboard/dashboard', ['kelasInfo' => $kelasInfo, 'kelasMapelId' => $kelasMapelId, 'totalSiswaUnique' => $totalSiswaUnique, 'totalSiswa' => $totalSiswa, 'assignedKelas' => $assignedKelas, 'user' => $profile, 'countKelas' => count($editorAccess), 'mapelKelas' => $mapelKelas, 'roles' => $roles, 'title' => 'Dashboard']); // Menampilkan halaman dasbor pengajar dengan data yang dikumpulkan
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani pengecualian dekripsi
                abort(404); // Mengembalikan halaman 404 jika terjadi kesalahan dekripsi
            }
        } elseif ($authRoles == 3) { // Jika pengguna adalah Siswa (roles_id = 3)
            return redirect('home'); // Mengarahkan pengguna ke halaman home
        }
    }

    /**
     * Menampilkan halaman dasbor. Pengguna akan diarahkan berdasarkan roles id mereka.
     *
     * @return \Illuminate\View\View
     */
    public function viewHome() // Mendefinisikan metode viewHome
    {
        // Mengumpulkan beberapa informasi tentang pengguna.
        $authRoles = $this->getAuthId(); // Mendapatkan ID peran pengguna yang sedang login
        $authRolesName = $this->getRolesName(); // Mendapatkan nama peran pengguna yang sedang login

        try {
            // $id = Crypt::decrypt($token);

            $roles = DashboardController::getRolesName(); // Mendapatkan nama peran pengguna
            $profile = User::findOrFail(Auth()->User()->id); // Mendapatkan profil pengguna berdasarkan ID pengguna yang sedang login

            $kelas = Kelas::where('id', $profile->kelas_id)->first(); // Mendapatkan data kelas berdasarkan ID kelas pengguna

            $kelasMapel = KelasMapel::where('kelas_id', $kelas['id'])->get(); // Mendapatkan data kelas mapel berdasarkan ID kelas
            $mapelCollection = []; // Inisialisasi array kosong untuk koleksi mapel

            foreach ($kelasMapel as $key) { // Iterasi melalui data kelas mapel
                $mapel = Mapel::where('id', $key->mapel_id)->first(); // Mendapatkan data mapel berdasarkan ID mapel
                $editorAccess = EditorAccess::where(
                    'kelas_mapel_id',
                    $key->id
                )->first(); // Mendapatkan akses editor berdasarkan ID kelas mapel

                if ($editorAccess) { // Jika akses editor ditemukan
                    $editorAccess = $editorAccess['user_id']; // Mendapatkan ID pengguna dari akses editor
                    $pengajar = User::where('id', $editorAccess)->first(['id', 'name']); // Mendapatkan data pengajar berdasarkan ID pengguna
                    $pengajarNama = $pengajar['name']; // Mendapatkan nama pengajar
                    $pengajarId = $pengajar['id']; // Mendapatkan ID pengajar
                } else { // Jika akses editor tidak ditemukan
                    $pengajarNama = '-'; // Set nama pengajar menjadi "-"
                    $pengajarId = null; // Set ID pengajar menjadi null
                }

                $mapelCollection[] = [
                    'mapel_name' => $mapel['name'], // Menambahkan nama mapel ke koleksi mapel
                    'mapel_id' => $mapel['id'], // Menambahkan ID mapel ke koleksi mapel
                    'deskripsi' => $mapel['deskripsi'], // Menambahkan deskripsi mapel ke koleksi mapel
                    'gambar' => $mapel['gambar'], // Menambahkan gambar mapel ke koleksi mapel
                    'pengajar_id' => $pengajarId, // Menambahkan ID pengajar ke koleksi mapel
                    'pengajar_name' => $pengajarNama, // Menambahkan nama pengajar ke koleksi mapel
                ];
            }

            $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang ditugaskan

            return view('menu/siswa/home/home', ['assignedKelas' => $assignedKelas, 'title' => 'Home', 'roles' => $authRolesName, 'user' => $profile, 'kelas' => $kelas, 'mapelKelas' => $mapelCollection, 'roles' => $roles, 'title' => 'Profil']); // Menampilkan halaman home siswa dengan data yang dikumpulkan

            return view('menu.profile.profileSiswa', ['assignedKelas' => $assignedKelas, 'user' => $profile, 'kelas' => $kelas['name'], 'mapelKelas' => $mapelCollection, 'roles' => $roles, 'title' => 'Profil']); // Menampilkan halaman profil siswa dengan data yang dikumpulkan
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani pengecualian dekripsi
            abort(404); // Mengembalikan halaman 404 jika terjadi kesalahan dekripsi
        }
    }

    /**
     * Mendapatkan nama peran pengguna (digunakan dalam beberapa metode lain dalam kelas lain).
     * Ini merupakan akses dasar untuk mendapatkan peran yang akan dirender.
     *
     * @return string
     */
    public static function getRolesName() // Mendefinisikan metode getRolesName
    {
        // Dapatkan ID -> Kueri -> Kembalikan sebagai string
        $authRoles = Auth()->User()->roles_id; // Mendapatkan ID peran pengguna yang sedang login
        $authRolesName = Role::where('id', $authRoles)->first('name'); // Mendapatkan nama peran berdasarkan ID peran
        $authRolesName = $authRolesName['name']; // Mendapatkan nama peran dari hasil query

        return $authRolesName; // Mengembalikan nama peran
    }

    /**
     * Mendapatkan roles id (jarang digunakan dalam kelas lain).
     *
     * @return int
     */
    public static function getAuthId() // Mendefinisikan metode getAuthId
    {
        return Auth()->User()->roles_id; // Mengembalikan ID peran pengguna yang sedang login
    }

    /**
     * Mendapatkan nama peran pengguna (digunakan dalam beberapa metode lain dalam kelas lain).
     * Ini merupakan akses dasar untuk mendapatkan peran yang akan dirender.
     *
     * @return array
     */
    public static function getAssignedClass() // Mendefinisikan metode getAssignedClass
    {
        $authRoles = Auth()->User()->roles_id; // Mendapatkan ID peran pengguna yang sedang login

        // Pengkondisian
        // Roles_id : 1 = Admin
        // Roles_id : 2 = Pengajar
        // Roles_id : 3 = Siswa
        if ($authRoles == 1) { // Jika pengguna adalah Admin
            return null; // Mengembalikan null
        } elseif ($authRoles == 2) { // Jika pengguna adalah Pengajar
            try {
                // Dapatkan ID Pengguna
                $id = Auth()->User()->id; // Mendapatkan ID pengguna yang sedang login

                // Kueri
                $profile = User::findOrFail($id); // Mendapatkan profil pengguna berdasarkan ID
                $editorAccess = EditorAccess::where('user_id', $id)->get(); // Mendapatkan akses editor untuk pengguna

                // Inisialisasi Array Kosong
                $mapelKelas = []; // Inisialisasi array kosong untuk mata pelajaran kelas

                // Membangun Data yang berkaitan dengan Pengguna dan apa yang mereka Ajar.
                // Sehingga akan muncul di Dasbor apa yang mereka ajar (Editor Access).
                foreach ($editorAccess as $key) { // Iterasi melalui akses editor
                    $kelasMapel = KelasMapel::where('id', $key->kelas_mapel_id)->first(); // Mendapatkan kelas mapel berdasarkan ID akses editor

                    if ($kelasMapel) { // Jika kelas mapel ditemukan
                        $mapelID = $kelasMapel->mapel_id; // Mendapatkan ID mata pelajaran
                        $kelasID = $kelasMapel->kelas_id; // Mendapatkan ID kelas

                        // Pemeriksa Mapel
                        $mapelKey = array_search($mapelID, array_column($mapelKelas, 'mapel_id')); // Mencari indeks mapel dalam array

                        if ($mapelKey !== false) { // Jika mapel ditemukan dalam array
                            // Tambahkan ke Array
                            $mapelKelas[$mapelKey]['kelas'][] = Kelas::where('id', $kelasID)->first(); // Tambahkan kelas ke array mapel
                        } else { // Jika mapel tidak ditemukan dalam array
                            // Temukan Mapel
                            $mapelKelas[] = [
                                'mapel_id' => $mapelID, // Tambahkan ID mapel ke array
                                'mapel' => Mapel::where('id', $mapelID)->first(), // Tambahkan data mapel ke array
                                'kelas' => [Kelas::where('id', $kelasID)->first()], // Tambahkan kelas ke array mapel
                            ];
                        }
                    }
                }

                return $mapelKelas; // Mengembalikan array mapel kelas
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani pengecualian dekripsi
                abort(404); // Mengembalikan halaman 404 jika terjadi kesalahan dekripsi
            }
        } elseif ($authRoles == 3) { // Jika pengguna adalah Siswa
            try {
                // Dapatkan ID Pengguna
                $id = Auth()->User()->kelas_id; // Mendapatkan ID kelas pengguna yang sedang login

                // Kueri
                $kelasMapelId = KelasMapel::where('kelas_id', $id)->get(); // Mendapatkan data kelas mapel berdasarkan ID kelas

                // Inisialisasi Array Kosong
                $mapelKelas = []; // Inisialisasi array kosong untuk mata pelajaran kelas

                // Membangun Data yang berkaitan dengan Pengguna dan apa yang mereka Ajar.
                // Sehingga akan muncul di Dasbor apa yang mereka ajar (Editor Access).
                foreach ($kelasMapelId as $key) { // Iterasi melalui data kelas mapel
                    // Temukan Mapel
                    $mapelKelas[] = [
                        'mapel_id' => $key->mapel_id, // Tambahkan ID mapel ke array
                        'mapel' => Mapel::where('id', $key->mapel_id)->first(), // Tambahkan data mapel ke array
                        'kelas' => [Kelas::where('id', $key->kelas_id)->first()], // Tambahkan kelas ke array mapel
                    ];
                }

                // dd($mapelKelas);
                return $mapelKelas; // Mengembalikan array mapel kelas
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani pengecualian dekripsi
                abort(404); // Mengembalikan halaman 404 jika terjadi kesalahan dekripsi
            }
        }
    }

    /**
     * Mendapatkan nama peran pengguna (digunakan dalam beberapa metode lain dalam kelas lain).
     * Ini merupakan akses dasar untuk mendapatkan peran yang akan dirender.
     *
     * @return array
     */
    public static function getAssignedClassSiswa() // Mendefinisikan metode getAssignedClassSiswa
    {
        $authRoles = Auth()->User()->roles_id; // Mendapatkan ID peran pengguna yang sedang login

        // Pengkondisian
        // Roles_id : 1 = Admin
        // Roles_id : 2 = Pengajar
        // Roles_id : 3 = Siswa
        if ($authRoles == 1) { // Jika pengguna adalah Admin
            return null; // Mengembalikan null
        } elseif ($authRoles == 2) { // Jika pengguna adalah Pengajar
            try {
                // Dapatkan ID Pengguna
                $id = Auth()->User()->id; // Mendapatkan ID pengguna yang sedang login

                // Kueri
                $profile = User::findOrFail($id); // Mendapatkan profil pengguna berdasarkan ID
                $editorAccess = EditorAccess::where('user_id', $id)->get(); // Mendapatkan akses editor untuk pengguna

                // Inisialisasi Array Kosong
                $mapelKelas = []; // Inisialisasi array kosong untuk mata pelajaran kelas

                // Membangun Data yang berkaitan dengan Pengguna dan apa yang mereka Ajar.
                // Sehingga akan muncul di Dasbor apa yang mereka ajar (Editor Access).
                foreach ($editorAccess as $key) { // Iterasi melalui akses editor
                    $kelasMapel = KelasMapel::where('id', $key->kelas_mapel_id)->first(); // Mendapatkan kelas mapel berdasarkan ID akses editor

                    if ($kelasMapel) { // Jika kelas mapel ditemukan
                        $mapelID = $kelasMapel->mapel_id; // Mendapatkan ID mata pelajaran
                        $kelasID = $kelasMapel->kelas_id; // Mendapatkan ID kelas

                        // Pemeriksa Mapel
                        $mapelKey = array_search($mapelID, array_column($mapelKelas, 'mapel_id')); // Mencari indeks mapel dalam array

                        if ($mapelKey !== false) { // Jika mapel ditemukan dalam array
                            // Tambahkan ke Array
                            $mapelKelas[$mapelKey]['kelas'][] = Kelas::where('id', $kelasID)->first(); // Tambahkan kelas ke array mapel
                        } else { // Jika mapel tidak ditemukan dalam array
                            // Temukan Mapel
                            $mapelKelas[] = [
                                'mapel_id' => $mapelID, // Tambahkan ID mapel ke array
                                'mapel' => Mapel::where('id', $mapelID)->first(), // Tambahkan data mapel ke array
                                'kelas' => [Kelas::where('id', $kelasID)->first()], // Tambahkan kelas ke array mapel
                            ];
                        }
                    }
                }

                return $mapelKelas; // Mengembalikan array mapel kelas
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) { // Menangani pengecualian dekripsi
                abort(404); // Mengembalikan halaman 404 jika terjadi kesalahan dekripsi
            }
        } elseif ($authRoles == 3) { // Jika pengguna adalah Siswa
            return null; // Mengembalikan null
        }
    }
}
