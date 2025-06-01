<?php

namespace App\Livewire; // Mendefinisikan namespace untuk kelas Diskusi

use Livewire\Component; // Mengimport kelas Component dari Livewire
use App\Models\Komentar; // Mengimport model Komentar dari namespace App\Models
use Illuminate\Support\Facades\Auth; // Mengimport kelas Auth dari Illuminate\Support\Facades

class Diskusi extends Component // Mendefinisikan kelas Diskusi yang merupakan turunan dari Component
{
    public $diskusiId; // Mendefinisikan properti diskusiId
    public $pesan; // Mendefinisikan properti pesan
    public $komentars; // Mendefinisikan properti komentars

    protected $rules = [ // Mendefinisikan aturan validasi untuk properti pesan
        'pesan' => 'required|string|max:500',
    ];

    public function mount($diskusiId) // Mendefinisikan fungsi mount dengan parameter $diskusiId
    {
        $this->diskusiId = $diskusiId; // Mengisi nilai properti diskusiId dengan nilai dari parameter
        $this->loadKomentars(); // Memanggil fungsi loadKomentars
    }

    public function loadKomentars() // Mendefinisikan fungsi loadKomentars
    {
        $this->komentars = Komentar::where('diskusi_id', $this->diskusiId)->oldest()->get(); // Mengisi properti komentars dengan data Komentar yang memiliki diskusi_id sesuai dengan diskusiId dan diurutkan dari yang terlama
    }
    
    public function submit() // Mendefinisikan fungsi submit
    {
        $this->validate(); // Melakukan validasi

        Komentar::create([ // Membuat data Komentar baru
            'diskusi_id' => $this->diskusiId, // Mengisi kolom diskusi_id dengan nilai dari properti diskusiId
            'user_id' => Auth::id(), // Mengisi kolom user_id dengan ID pengguna yang sedang login
            'pesan' => $this->pesan, // Mengisi kolom pesan dengan nilai dari properti pesan
        ]);

        $this->pesan = ''; // Mengosongkan nilai properti pesan
        $this->loadKomentars(); // Memanggil fungsi loadKomentars
    }

    public function render() // Mendefinisikan fungsi render
    {
        return view('livewire.diskusi'); // Mengembalikan tampilan livewire.diskusi
    }
}
