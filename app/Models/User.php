<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Botユーザーを取得する（存在しない場合はnull）
     */
    public static function getBot(): ?self
    {
        // 1. Configのメールアドレスで検索
        $email = config('services.bot.email');
        if ($email) {
            $bot = self::where('email', $email)->first();
            if ($bot)
                return $bot;
        }

        // 2. 名前でフォールバック検索
        return self::where('name', 'クイックン')->first();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'status_updated_at',
        'show_bot_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * UserはTripを通じてPhotoをたくさん持っている
     */
    public function photos()
    {
        return $this->hasManyThrough(Photo::class, Trip::class);
    }

    /**
     * パートナー（自分以外のユーザー）を取得
     * 現状は簡易的に「自分以外の最初の一人」とする
     */

    /**
     * パートナー（自分以外のユーザー）を取得
     * 現状は簡易的に「自分以外の最初の一人」とする
     */
    public function getPartnerAttribute()
    {
        return self::where('id', '!=', $this->id)->first();
    }

    /**
     * プロフィール画像のURLを取得
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function badges()
    {
        return $this
            ->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('obtained_at')
            ->withTimestamps();
    }

    public function scraps(): HasMany
    {
        return $this->hasMany(Scrap::class);
    }
}
