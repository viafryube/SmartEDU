<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'kelas_id',
        'roles_id',
        'password',
        'gambar',
        'deskripsi',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function DataSiswa()
    {
        return $this->belongsTo(DataSiswa::class);
    }

    public function Role()
    {
        return $this->belongsTo(Role::class);
    }

    public function notification()
    {
        return $this->hasMany(Notification::class);
    }

    public function Contact()
    {
        return $this->hasOne(Contact::class);
    }

    public function EditorAccess()
    {
        return $this->hasMany(EditorAccess::class);
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function User()
    {
        return $this->hasMany(Tugas::class);
    }

    public function UserTugas()
    {
        return $this->hasMany(UserTugas::class);
    }

    public function UserMateri()
    {
        return $this->hasMany(UserMateri::class);
    }

    public function UserJawaban()
    {
        return $this->hasMany(UserJawaban::class);
    }

    public function UserCommit()
    {
        return $this->hasMany(UserCommit::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_user_id');
    }

    // Jika ingin mendapatkan semua pesan, baik yang dikirim maupun diterima
    public function messages()
    {
        return $this->hasMany(Message::class, 'from_user_id')
                    ->orWhere('to_user_id', $this->id);
    }

    public function Komentar()
    {
        return $this->hasMany(Komentar::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponses::class);
    }
}
