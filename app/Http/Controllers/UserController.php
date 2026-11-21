<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('id')->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.form', ['user' => new User()]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::query()->create($request->safe()->only(['username', 'full_name', 'email', 'password']));

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user, AvatarService $avatars): RedirectResponse
    {
        $data = $request->safe()->only(['username', 'full_name', 'email', 'password']);
        // A blank edit password keeps the current hash.
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $newAvatar = null;
        $oldAvatar = null;

        try {
            if ($request->hasFile('avatar')) {
                $newAvatar = $avatars->store($request->file('avatar'));
            }

            DB::transaction(function () use ($user, $data, $newAvatar, &$oldAvatar): void {
                // Serialize concurrent updates before choosing which old file to remove.
                $record = User::query()->lockForUpdate()->findOrFail($user->getKey());
                $oldAvatar = $record->avatar;
                $previousEmail = $record->email;
                $record->fill($data);
                if ($record->isDirty('email')) {
                    $record->email_verified_at = null;
                    if ($previousEmail !== null) {
                        DB::table('password_reset_tokens')->where('email', $previousEmail)->delete();
                    }
                }
                if (isset($data['password'])) {
                    $record->remember_token = Str::random(60);
                    if ($previousEmail !== null) {
                        DB::table('password_reset_tokens')->where('email', $previousEmail)->delete();
                    }
                }
                if ($newAvatar !== null) {
                    $record->avatar = $newAvatar;
                }
                $record->save();
            });
        } catch (Throwable $exception) {
            // A failed database write must not leave a new unused avatar behind.
            $avatars->delete($newAvatar);
            throw $exception;
        }

        if ($newAvatar !== null) {
            $avatars->delete($oldAvatar);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user, AvatarService $avatars): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'You cannot delete the account you are currently using.');
        $oldAvatar = null;

        DB::transaction(function () use ($user, &$oldAvatar): void {
            $record = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $oldAvatar = $record->avatar;
            if ($record->email !== null) {
                DB::table('password_reset_tokens')->where('email', $record->email)->delete();
            }
            $record->delete();
        });
        $avatars->delete($oldAvatar);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
