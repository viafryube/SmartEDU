<?php

namespace App\Http\Controllers; // Mendefinisikan namespace untuk controller

use App\Models\Kelas; // Menggunakan model Kelas
use App\Models\Survey; // Menggunakan model Survey
use App\Models\SurveyQuestion; // Menggunakan model SurveyQuestion
use App\Models\SurveyResponses; // Menggunakan model SurveyResponses
use App\Models\User; // Menggunakan model User
use Illuminate\Http\Request; // Menggunakan class Request dari Illuminate
use Illuminate\Support\Facades\Auth; // Menggunakan class Auth dari Illuminate\Support\Facades
use Illuminate\Support\Facades\Validator; // Menggunakan class Validator dari Illuminate\Support\Facades

class SurveyController extends Controller // Mendefinisikan class SurveyController yang merupakan turunan dari Controller
{
    // Menampilkan halaman daftar survei untuk admin
    public function viewSurvey() // Fungsi untuk menampilkan halaman daftar survei
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController

        $surveys = Survey::with('user', 'kelas')->paginate(15); // Mengambil data survei dengan relasi user dan kelas
        return view('menu.admin.controlSurvey.viewSurvey', ['title' => 'Data Survey', 'surveys' => $surveys, 'roles' => $roles]); // Menampilkan view daftar survei dengan data yang diperlukan
    }

    // Menampilkan halaman tambah survei untuk admin
    public function viewTambahSurvey() // Fungsi untuk menampilkan halaman tambah survei
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        $users = User::where('roles_id', 2)->get(); // Mengambil semua guru
        $classes = Kelas::all(); // Mengambil semua kelas
        return view('menu.admin.controlSurvey.viewTambahSurvey', ['title' => 'Tambah Survey', 'users' => $users, 'classes' => $classes, 'roles' => $roles]); // Menampilkan view tambah survei dengan data yang diperlukan
    }

    public function viewUpdateSurvey($id) // Fungsi untuk menampilkan halaman update survei
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        $survey = Survey::with('user', 'kelas')->findOrFail($id); // Mengambil data survei berdasarkan ID
        $users = User::where('roles_id', 2)->get(); // Mengambil semua guru
        $classes = Kelas::all(); // Mengambil semua kelas

        return view('menu.admin.controlSurvey.viewUpdateSurvey', ['title' => 'Update Survey', 'survey' => $survey, 'users' => $users, 'classes' => $classes, 'roles' => $roles]); // Menampilkan view update survei dengan data yang diperlukan
    }

    // Menyimpan survei baru
    public function tambahSurvey(Request $request) // Fungsi untuk menyimpan survei baru
    {
        $validator = Validator::make($request->all(), [ // Membuat validasi data pendaftaran
            'user_id' => 'required|exists:users,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        if ($validator->fails()) { // Jika validasi gagal
            return redirect()->back()->withErrors($validator)->withInput(); // Redirect kembali dengan error dan input sebelumnya
        }

        $data = [
            'user_id' => $request->user_id,
            'kelas_id' => $request->kelas_id,
            'status' => 'Belum',
        ];

        Survey::create($data); // Menyimpan data survei baru

        return redirect()->route('viewSurvey')->with('success', 'Survey berhasil ditambahkan!'); // Redirect ke halaman daftar survei dengan pesan sukses
    }

    public function updateSurvey(Request $request) // Fungsi untuk update survei
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $survey = Survey::findOrFail($request->id);
        $survey->update([
            'user_id' => $request->user_id,
            'kelas_id' => $request->kelas_id,
            'status' => $survey->status, // Biarkan status tetap sama
        ]);

        return redirect()->route('viewSurvey')->with('success', 'Survey berhasil diperbarui!');
    }

    public function viewListSurvey($surveyId) // Fungsi untuk menampilkan daftar survei
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        $survey = Survey::with('user', 'kelas')->findOrFail($surveyId); // Mengambil data survei berdasarkan ID
        $responses = SurveyResponses::where('survey_id', $surveyId)->with('user')->get(); // Mengambil respon survei berdasarkan ID survei

        // Mengelompokkan respon berdasarkan user_id untuk memastikan setiap siswa hanya muncul satu kali
        $groupedResponses = $responses->groupBy('user_id'); // Mengelompokkan respon berdasarkan user_id

        return view('menu.admin.controlSurvey.viewListSurvey', [
            'title' => 'List Responden Survey',
            'survey' => $survey,
            'responses' => $groupedResponses,
            'roles' => $roles,
        ]);
    }

    public function viewDetailSurvey($surveyId, $userId) // Fungsi untuk menampilkan detail survei
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        $survey = Survey::with('user', 'kelas')->findOrFail($surveyId); // Mengambil data survei berdasarkan ID
        $responses = SurveyResponses::where('survey_id', $surveyId)
            ->where('user_id', $userId)
            ->with('question')
            ->get(); // Mengambil respon survei berdasarkan ID survei dan ID user
        $student = User::findOrFail($userId); // Mengambil data siswa berdasarkan ID user

        return view('menu.admin.controlSurvey.viewDetailSurvey', [
            'title' => 'Detail Survey Siswa',
            'survey' => $survey,
            'responses' => $responses,
            'student' => $student,
            'roles' => $roles,
        ]);
    }

    public function viewSurveyMurid() // Fungsi untuk menampilkan halaman survei untuk murid
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang diassign
        $profile = User::findOrFail(Auth()->User()->id); // Mendapatkan data profil user yang sedang login
        $kelas = Kelas::where('id', $profile->kelas_id)->first(); // Mengambil data kelas berdasarkan ID

        $surveys = Survey::with('user', 'kelas')->paginate(15); // Mengambil data survei dengan relasi user dan kelas
        return view('menu.siswa.survey.viewSurvey', ['title' => 'Data Survey', 'assignedKelas' => $assignedKelas, 'surveys' => $surveys, 'roles' => $roles, 'profile' => $profile, 'kelas' => $kelas]); // Menampilkan view survei untuk murid dengan data yang diperlukan
    }

    // Menampilkan halaman survei untuk murid
    public function viewSurveyStart() // Fungsi untuk menampilkan halaman survei awal untuk murid
    {
        $user = Auth::user(); // Mendapatkan data user yang sedang login
        $survey = Survey::where('kelas_id', $user->kelas_id)->first(); // Mengambil data survei berdasarkan kelas user

        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        $assignedKelas = DashboardController::getAssignedClass(); // Mendapatkan kelas yang diassign
        $profile = User::findOrFail(Auth()->User()->id); // Mendapatkan data profil user yang sedang login
        $kelas = Kelas::where('id', $profile->kelas_id)->first(); // Mengambil data kelas berdasarkan ID
        $questions = SurveyQuestion::all(); // Mengambil semua pertanyaan survei
        return view('menu.siswa.survey.survey', ['title' => 'Survey', 'survey' => $survey, 'questions' => $questions, 'roles' => $roles, 'profile' => $profile, 'assignedKelas' => $assignedKelas, 'kelas' => $kelas]); // Menampilkan view survei awal untuk murid dengan data yang diperlukan
    }

    // Menyimpan respons survei murid
    public function submitSurveyMurid(Request $request) // Fungsi untuk menyimpan respons survei dari murid
    {
        $user = Auth::user(); // Mendapatkan data user yang sedang login
        $survey = Survey::where('kelas_id', $user->kelas_id)->first(); // Mengambil data survei berdasarkan kelas user

        foreach ($request->responses as $question_id => $response) { // Looping untuk setiap respons
            SurveyResponses::create([ // Membuat data respons baru
                'survey_id' => $survey->id,
                'user_id' => $user->id,
                'question_id' => $question_id,
                'response' => $response,
            ]);
        }

        Survey::where('id', $survey->id)->update(['status' => 'selesai']); // Update status survei menjadi selesai

        return redirect()->route('viewSurveyMurid')->with('success', 'Survey berhasil dikirim!'); // Redirect ke halaman survei murid dengan pesan sukses
    }

    public function searchSurvey(Request $request) // Fungsi untuk mencari survei
    {
        $search = $request->input('search'); // Mendapatkan inputan pencarian

        $surveys = Survey::where('guru', 'like', '%' . $query . '%') // Mencari survei berdasarkan nama guru
            ->orWhere('kelas', 'like', '%' . $query . '%') // Mencari survei berdasarkan nama kelas
            ->orWhere('deskripsi', 'like', '%' . $query . '%') // Mencari survei berdasarkan deskripsi
            ->paginate(10); // Pagination hasil pencarian survei

        return view('menu.admin.controlSurvey.viewSurvey', compact('surveys'))->render(); // Menampilkan view daftar survei dengan hasil pencarian
    }

    public function destroySurvey(Request $request) // Fungsi untuk menghapus survei
    {
        $surveyId = $request->input('idHapus'); // Mendapatkan ID survei yang akan dihapus
        $survey = Survey::findOrFail($surveyId); // Mengambil data survei berdasarkan ID

        // Hapus semua respons survei terkait
        SurveyResponses::where('survey_id', $surveyId)->delete(); // Menghapus semua respons survei terkait

        // Hapus survei
        $survey->delete(); // Menghapus survei

        return redirect()->route('viewSurvey')->with('delete-success', 'Survey berhasil dihapus!'); // Redirect ke halaman daftar survei dengan pesan sukses
    }

    // Menampilkan halaman tambah pertanyaan survei untuk admin
    public function viewTambahSurveyQuestion() // Fungsi untuk menampilkan halaman tambah pertanyaan survei
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        return view('menu.admin.controlSurvey.viewTambahSurveyQuestion', ['title' => 'Tambah Pertanyaan Survey', 'roles' => $roles]); // Menampilkan view tambah pertanyaan survei dengan data yang diperlukan
    }

// Menyimpan pertanyaan survei baru
    public function tambahSurveyQuestion(Request $request) // Fungsi untuk menyimpan pertanyaan survei baru
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        SurveyQuestion::create([
            'question' => $request->question,
        ]);

        return redirect()->route('viewSurveyQuestions')->with('success', 'Pertanyaan Survey berhasil ditambahkan!');
    }

// Menampilkan halaman daftar pertanyaan survei untuk admin
    public function viewSurveyQuestions() // Fungsi untuk menampilkan halaman daftar pertanyaan survei
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        $questions = SurveyQuestion::paginate(15); // Mengambil data pertanyaan survei dengan pagination
        return view('menu.admin.controlSurvey.viewSurveyQuestions', ['title' => 'Data Pertanyaan Survey', 'questions' => $questions, 'roles' => $roles]); // Menampilkan view daftar pertanyaan survei dengan data yang diperlukan
    }

    public function destroySurveyQuestion(Request $request) // Fungsi untuk menghapus pertanyaan survei
    {
        $questionId = $request->input('idHapus'); // Mendapatkan ID pertanyaan yang akan dihapus
        $question = SurveyQuestion::findOrFail($questionId); // Mengambil data pertanyaan survei berdasarkan ID

        // Hapus pertanyaan
        $question->delete(); // Menghapus pertanyaan

        return redirect()->route('viewSurveyQuestions')->with('delete-success', 'Pertanyaan berhasil dihapus!'); // Redirect ke halaman daftar pertanyaan survei dengan pesan sukses
    }

    public function viewUpdateSurveyQuestion($id) // Fungsi untuk menampilkan halaman update pertanyaan survei
    {
        $roles = DashboardController::getRolesName(); // Mendapatkan nama peran dari DashboardController
        $question = SurveyQuestion::findOrFail($id); // Mengambil data pertanyaan survei berdasarkan ID
        return view('menu.admin.controlSurvey.viewUpdateSurveyQuestion', ['title' => 'Update Pertanyaan Survey', 'question' => $question, 'roles' => $roles]); // Menampilkan view update pertanyaan survei dengan data yang diperlukan
    }
    public function updateSurveyQuestion(Request $request) // Fungsi untuk update pertanyaan survei
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $question = SurveyQuestion::findOrFail($request->id);
        $question->update([
            'question' => $request->question,
        ]);

        return redirect()->route('viewSurveyQuestions')->with('success', 'Pertanyaan berhasil diperbarui!');
    }

}
