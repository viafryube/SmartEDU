<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diskusi extends Model
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

    public function KelasMapel()
    {
        return $this->belongsTo(KelasMapel::class);
    }

    public function Komentar()
    {
        return $this->hasMany(Komentar::class);
    }
}
