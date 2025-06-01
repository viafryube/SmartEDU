<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    use HasFactory;

    protected $fillable = [
        'diskusi_id',
        'user_id',
        'pesan',
    ];

    protected $guarded = [
        'id',
    ];

    public function Diskusi()
    {
        return $this->belongsTo(Diskusi::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }


}
