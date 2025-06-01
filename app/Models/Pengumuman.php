<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;
    protected $fillable = [
        'kelas_mapel_id',
        'name',
        'content',
        'isHidden',
    ];

    protected $guarded = [
        'id',
    ];
}
