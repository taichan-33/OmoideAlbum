<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon_path',
        'condition_type',
        'condition_value',
    ];

    protected $casts = [
        'condition_value' => 'array',
    ];

    public function users()
    {
        return $this
            ->belongsToMany(User::class, 'user_badges')
            ->withPivot('obtained_at')
            ->withTimestamps();
    }
}
