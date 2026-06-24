<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Illuminate\Support\Facades\Log;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Log::info('UpdateUserProfileInformation called', ['user_id' => $user->id, 'has_photo' => isset($input['photo'])]);
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'confirm_email' => [
                Rule::excludeIf(fn () => ($input['email'] ?? null) === $user->email),
                Rule::requiredIf(fn () => ($input['email'] ?? null) !== $user->email),
                'nullable',
                'same:email',
            ],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            try {
                Log::info('Attempting to update profile photo', ['user_id' => $user->id, 'photo' => gettype($input['photo'])]);
                $user->updateProfilePhoto($input['photo']);
                Log::info('Profile photo updated', ['user_id' => $user->id, 'path' => $user->profile_photo_path]);
            } catch (\Throwable $e) {
                Log::error('Failed to update profile photo', ['user_id' => $user->id, 'exception' => $e->getMessage()]);
                throw $e;
            }
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
