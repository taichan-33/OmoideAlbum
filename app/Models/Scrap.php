<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scrap extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'title',
        'description',
        'image_url',
        'site_name',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
