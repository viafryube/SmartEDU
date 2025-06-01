<?php
namespace App\Livewire; // Mendefinisikan namespace untuk kelas Chat

use Livewire\Component; // Menggunakan kelas Livewire Component
use App\Models\Message; // Menggunakan model Message
use App\Models\User; // Menggunakan model User
use Illuminate\Support\Facades\Auth; // Menggunakan fasad Auth dari Laravel

class Chat extends Component // Mendefinisikan kelas Chat yang merupakan turunan dari Livewire Component
{
    public $messages; // Properti untuk menyimpan daftar pesan
    public $messageText = ''; // Properti untuk menyimpan teks pesan yang akan dikirim
    public $selectedUser; // Properti untuk menyimpan user yang dipilih
    public $selectedUserId; // Properti untuk menyimpan ID user yang dipilih
    public $unreadMessagesCount = []; // Properti untuk menyimpan jumlah pesan yang belum dibaca

    public function mount() // Method yang dipanggil saat komponen di-mount
    {
        $this->selectedUserId = User::where('id', '!=', Auth::id())->first()->id; // Mengatur selectedUserId dengan ID user selain user yang sedang login
        $this->loadMessages(); // Memuat pesan-pesan
        $this->loadUnreadMessagesCount(); // Memuat jumlah pesan yang belum dibaca
    }

    public function loadMessages() // Method untuk memuat pesan-pesan
    {
        $this->messages = Message::where(function($query) { // Mengambil pesan dari user yang sedang login ke user yang dipilih
            $query->where('from_user_id', Auth::id())
                  ->where('to_user_id', $this->selectedUserId);
        })->orWhere(function($query) { // Atau mengambil pesan dari user yang dipilih ke user yang sedang login
            $query->where('from_user_id', $this->selectedUserId)
                  ->where('to_user_id', Auth::id());
        })->with('fromUser', 'toUser')->get(); // Mengambil relasi fromUser dan toUser dari pesan-pesan
    }

    public function loadUnreadMessagesCount() // Method untuk memuat jumlah pesan yang belum dibaca
    {
        $this->unreadMessagesCount = Message::where('to_user_id', Auth::id()) // Mengambil pesan yang ditujukan ke user yang sedang login dan belum dibaca
                                            ->where('is_read', false)
                                            ->groupBy('from_user_id')
                                            ->selectRaw('from_user_id, COUNT(*) as count')
                                            ->pluck('count', 'from_user_id')
                                            ->toArray(); // Mengubah hasil query menjadi array
    }

    public function selectUser($userId) // Method untuk memilih user
    {
        $this->selectedUserId = $userId; // Mengatur selectedUserId dengan ID user yang dipilih
        $this->loadMessages(); // Memuat pesan-pesan
        Message::where('from_user_id', $userId) // Menandai pesan dari user yang dipilih ke user yang sedang login sebagai sudah dibaca
               ->where('to_user_id', Auth::id())
               ->update(['is_read' => true]);
        $this->loadUnreadMessagesCount(); // Memuat jumlah pesan yang belum dibaca
    }

    public function sendMessage() // Method untuk mengirim pesan
    {
        if ($this->messageText != '') { // Jika teks pesan tidak kosong
            Message::create([ // Membuat pesan baru
                'from_user_id' => Auth::id(),
                'to_user_id' => $this->selectedUserId,
                'message' => $this->messageText,
                'is_read' => false,
            ]);

            $this->loadMessages(); // Memuat pesan-pesan
            $this->messageText = ''; // Mengosongkan teks pesan
        }
    }

    public function render() // Method untuk merender tampilan
    {
        return view('livewire.chat', [ // Mengembalikan tampilan chat dengan data users
            'users' => User::where('id', '!=', Auth::id())->get(),
        ]);
    }
}
