<?php

namespace App\Support;

final class AvatarPath
{
    public static function isManaged(?string $path): bool
    {
        return is_string($path) && preg_match(
            '~\Aavatars/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.jpg\z~',
            $path,
        ) === 1;
    }
}
