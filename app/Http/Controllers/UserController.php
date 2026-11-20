<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        User::query()->create($request->safe()->only(['username', 'full_name']));

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user, AvatarService $avatars): RedirectResponse
    {
        $data = $request->safe()->only(['username', 'full_name']);
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
                $record->fill($data);
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
}
