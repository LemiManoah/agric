<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FarmerPhotoService
{
    public function replacePhoto(?UploadedFile $photo, ?string $existingPath = null): ?string
    {
        if ($photo === null) {
            return $existingPath;
        }

        $this->guardUploadedPhoto($photo);

        if ($existingPath) {
            $this->deletePhoto($existingPath);
        }

        return $photo->store('farmer-passports', $this->diskName());
    }

    public function deletePhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk($this->diskName())->delete($path);
    }

    private function guardUploadedPhoto(UploadedFile $photo): void
    {
        if (! $photo->isValid()) {
            throw ValidationException::withMessages([
                'passport_photo' => 'The uploaded passport photo is invalid.',
            ]);
        }
    }

    private function diskName(): string
    {
        return (string) config('filesystems.default', 'public');
    }
}
