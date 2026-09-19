<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface ProfileServiceInterface
{
    /**
     * Save profile data for the user. Marks the profile as completed
     * the first time it is saved, if it wasn't already.
     *
     * @param  array{name?: string, email?: string, mobile_number?: string, preferred_role?: string, photo?: UploadedFile}  $data
     */
    public function update(User $user, array $data): User;
}
