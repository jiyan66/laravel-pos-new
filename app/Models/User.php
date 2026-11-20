<?php

namespace App\Models;

use App\Support\AvatarPath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;

class User extends Model
{
    // A browser-supplied avatar path must never be mass assigned.
    protected $fillable = ['username', 'full_name'];

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
