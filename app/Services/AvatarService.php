<?php

namespace App\Services;

use App\Support\AvatarPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class AvatarService
{
    public function store(UploadedFile $file): string
    {
        try {
            $manager = new ImageManager(new Driver(), autoOrientation: true, decodeAnimation: false);
            $encoded = $manager->read($file->getPathname())
                ->cover(256, 256)
                ->toJpeg(quality: 85, strip: true);
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'avatar' => 'The picture could not be prepared. Please choose another JPG or PNG image.',
            ]);
        }

        $path = 'avatars/'.Str::uuid().'.jpg';

        try {
            if (! Storage::disk('public')->put($path, (string) $encoded)) {
                throw new RuntimeException('The avatar could not be written to the public disk.');
            }
        } catch (Throwable $exception) {
            $this->delete($path);
            report($exception);
            throw ValidationException::withMessages([
                'avatar' => 'The picture could not be saved. Please try again or contact the site owner.',
            ]);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! AvatarPath::isManaged($path)) {
            return;
        }

        try {
            if (! Storage::disk('public')->delete($path)) {
                report(new RuntimeException('An old avatar file could not be removed.'));
            }
        } catch (Throwable $exception) {
            // Cleanup failure must not undo a successfully committed profile update.
            report($exception);
        }
    }
}
