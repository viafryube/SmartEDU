<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekomendasiFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'rekomendasi_id',
        'file',
    ];

    public function Rekomendasi()
    {
        return $this->belongsTo(Rekomendasi::class);
    }
}
