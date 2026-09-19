<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\ProfileServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService implements ProfileServiceInterface
{
    public function update(User $user, array $data): User
    {
        if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $data['profile_photo_path'] = $data['photo']->store('profile-photos', 'public');
        }

        unset($data['photo']);

        if (! $user->hasCompletedProfile()) {
            $data['profile_completed_at'] = now();
        }

        $user->update($data);

        return $user->refresh();
    }
}
