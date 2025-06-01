<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'message',
        'is_read',  // Pastikan kolom ini ada di tabel messages
    ];

    // Relasi dengan model User sebagai pengirim pesan
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    // Relasi dengan model User sebagai penerima pesan
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
