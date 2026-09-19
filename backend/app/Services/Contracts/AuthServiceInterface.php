<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Validation\ValidationException;

interface AuthServiceInterface
{
    /**
     * Create a new user account from validated registration data.
     *
     * @param  array{name: string, email: string, mobile_number: string, password: string}  $data
     */
    public function register(array $data): User;

    /**
     * Resolve the user matching the given credentials.
     *
     * @throws ValidationException when the credentials are invalid.
     */
    public function attemptCredentials(string $email, string $password): User;

    /**
     * Send a password reset link to the given email, if it belongs to a user.
     *
     * @return string The password broker status constant.
     */
    public function sendPasswordResetLink(string $email): string;

    /**
     * Reset a user's password using a valid reset token.
     *
     * @param  array{token: string, email: string, password: string}  $data
     * @return string The password broker status constant.
     */
    public function resetPassword(array $data): string;
}
