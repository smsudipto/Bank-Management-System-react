<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        return view('customer.profile.index', [
            'user' => $user,
            'account' => $user->bankAccount,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $newPhotoPath = null;
        $oldPhotoPath = $user->profile_photo_path;
        $removePhoto = $request->boolean('remove_profile_photo');

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')->store('profile-photos', 'local');
        }

        try {
            DB::transaction(function () use ($user, $validated, $newPhotoPath, $removePhoto): void {
                $user->update([
                    'name' => $validated['name'],
                    'email' => strtolower($validated['email']),
                    'phone' => $validated['phone'] ?? null,
                    'profile_photo_path' => $newPhotoPath
                        ?? ($removePhoto ? null : $user->profile_photo_path),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($newPhotoPath !== null) {
                Storage::disk('local')->delete($newPhotoPath);
            }

            throw $exception;
        }

        if (($newPhotoPath !== null || $removePhoto) && $oldPhotoPath !== null) {
            Storage::disk('local')->delete($oldPhotoPath);
        }

        return redirect()->route('customer.profile.index')
            ->with('success', 'Your profile has been updated successfully.');
    }

    public function photo(Request $request)
    {
        $user = $request->user();

        abort_unless($user->hasProfilePhoto() && Storage::disk('local')->exists($user->profile_photo_path), 404);

        return response()->file(Storage::disk('local')->path($user->profile_photo_path));
    }
}
