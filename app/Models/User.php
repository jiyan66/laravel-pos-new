<?php

namespace App\Models;

use App\Support\AvatarPath;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class User extends Authenticatable
{
    use Notifiable;

    // Avatar paths are still assigned only by the server-side upload workflow.
    protected $fillable = ['username', 'full_name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'email_verified_at' => 'datetime'];
    }

    // Compatibility for Breeze's default display-name references.
    public function getNameAttribute(): string
    {
        return (string) $this->full_name;
    }

    public function getAvatarUrlAttribute(): string
    {
        if (AvatarPath::isManaged($this->avatar)) {
            try {
                if (Storage::disk('public')->exists($this->avatar)) {
                    return route('avatars.show', ['filename' => basename($this->avatar)]);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return asset('images/avatar-placeholder.svg');
    }
}
