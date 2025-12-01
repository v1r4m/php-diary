<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'body_ciphertext',
        'salt',
        'iv',
    ];

    /**
     * Get the user that owns the diary.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
