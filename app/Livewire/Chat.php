<?php
namespace App\Livewire; // Mendefinisikan namespace untuk kelas Chat

use Livewire\Component; // Menggunakan kelas Livewire Component
use App\Models\Message; // Menggunakan model Message
use App\Models\User; // Menggunakan model User
use Illuminate\Support\Facades\Auth as AuthChat; // Menggunakan fasad Auth dari Laravel
use Illuminate\Support\Facades\Log; 

class Chat extends Component // Mendefinisikan kelas Chat yang merupakan turunan dari Livewire Component
{
    public $messages; // Properti untuk menyimpan daftar pesan
    public $messageText = ''; // Properti untuk menyimpan teks pesan yang akan dikirim
    public $selectedUser; // Properti untuk menyimpan user yang dipilih
    public $selectedUserId; // Properti untuk menyimpan ID user yang dipilih
    public $unreadMessagesCount = []; // Properti untuk menyimpan jumlah pesan yang belum dibaca
    public $users = [];
    public $search = '';


    public function mount() // Method yang dipanggil saat komponen di-mount
    {
        $this->selectedUserId = User::where('id', '!=', AuthChat::id())->first()->id; // Mengatur selectedUserId dengan ID user selain user yang sedang login
        $this->loadMessages(); // Memuat pesan-pesan
        $this->loadUnreadMessagesCount(); // Memuat jumlah pesan yang belum dibaca
        $this->loadUsers();
        $firstUser = User::where('id', '!=', AuthChat::id())->first();
        if ($firstUser) {
            $this->selectedUserId = $firstUser->id;
            $this->selectUser($firstUser->id);
        }

        $this->loadUnreadMessagesCount();
    }

    public function loadMessages() // Method untuk memuat pesan-pesan
    {
        $this->messages = Message::where(function($query) {
            $query->where('from_user_id', AuthChat::id())
                ->where('to_user_id', $this->selectedUserId);
        })->orWhere(function($query) {
            $query->where('from_user_id', $this->selectedUserId)
                ->where('to_user_id', AuthChat::id());
        })
        ->with('fromUser', 'toUser')
        ->orderBy('created_at', 'asc') // Urut berdasarkan waktu (lama ke baru)
        ->get();

    }

    public function loadUnreadMessagesCount() // Method untuk memuat jumlah pesan yang belum dibaca
    {
        $this->unreadMessagesCount = Message::where('to_user_id', AuthChat::id()) // Mengambil pesan yang ditujukan ke user yang sedang login dan belum dibaca
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
               ->where('to_user_id', AuthChat::id())
               ->update(['is_read' => true]);
        $this->loadUnreadMessagesCount(); // Memuat jumlah pesan yang belum dibaca
    }

    public function sendMessage() // Method untuk mengirim pesan
    {
        if ($this->messageText != '') { // Jika teks pesan tidak kosong
            Message::create([ // Membuat pesan baru
                'from_user_id' => AuthChat::id(),
                'to_user_id' => $this->selectedUserId,
                'message' => $this->messageText,
                'is_read' => false,
            ]);

            $this->loadMessages(); // Memuat pesan-pesan
            $this->messageText = ''; // Mengosongkan teks pesan
            $this->dispatch('$refresh');
        }
    }

    public function render()
    {
        $this->loadUsers(); // agar pencarian selalu update otomatis
    return view('livewire.chat', ['users' => $this->users]);
    }


    public function updatedSearch($value)
    {
         // log ke file
        logger('Search diperbarui', ['value' => $value]);

        // kirim ke browser
        $this->dispatch('search-updated', value: $value);
        Log::info('Search changed to: ' . $value);  
        $this->loadUsers();
    }


    public function loadUsers()
    {
        $authId = AuthChat::id();

        $users = User::where('id', '!=', $authId)
            ->where('name', 'like', '%' . $this->search . '%')
            ->with([
                'sentMessages' => function ($query) use ($authId) {
                    $query->where('to_user_id', $authId)->latest();
                },
                'receivedMessages' => function ($query) use ($authId) {
                    $query->where('from_user_id', $authId)->latest();
                }
            ])
            ->get();

        $this->users = $users->sortByDesc(function ($user) {
            $latestSent = $user->sentMessages->first()?->created_at;
            $latestReceived = $user->receivedMessages->first()?->created_at;
            return max($latestSent, $latestReceived);
        })->values();
        
    }
}
