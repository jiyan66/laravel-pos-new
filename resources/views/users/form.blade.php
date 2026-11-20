@extends('layouts.app')
@section('title', $user->exists ? 'Edit User' : 'Create User')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">USER ACCOUNTS</p><h1>{{ $user->exists ? 'Edit user' : 'Create user' }}</h1><p class="subtitle">Manage the details shown in the user directory.</p></div><a class="button button-light" href="{{ route('users.index') }}">Back to users</a></div>
    <section class="panel form-panel" aria-label="User form">
        <form method="POST" enctype="multipart/form-data" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if ($user->exists) @method('PUT') @endif
            <p class="form-note">Fields marked * are required.</p>
            <div class="field"><label for="username">Username *</label><input id="username" name="username" type="text" maxlength="50" required autocomplete="username" value="{{ old('username', $user->username) }}" @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>@error('username')<p class="field-error" id="username-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="full_name">Full name *</label><input id="full_name" name="full_name" type="text" maxlength="100" required autocomplete="name" value="{{ old('full_name', $user->full_name) }}" @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror>@error('full_name')<p class="field-error" id="full_name-error">{{ $message }}</p>@enderror</div>
            @if ($user->exists)
                <div class="field">
                    <label for="avatar">Profile picture</label>
                    <img class="avatar-preview" src="{{ $user->avatar_url }}" alt="Current avatar" width="96" height="96">
                    <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png" aria-describedby="avatar-help @error('avatar') avatar-error @enderror" @error('avatar') aria-invalid="true" @enderror>
                    <p class="field-help" id="avatar-help">JPG or PNG, up to 2 MB and 3000 × 3000 pixels. The image is cropped to a 256 × 256 avatar. Leave blank to keep the current picture. If validation fails, select the file again.</p>
                    @error('avatar')<p class="field-error" id="avatar-error">{{ $message }}</p>@enderror
                </div>
            @else
                <p class="form-note">You can add a profile picture on the edit page after creating this user.</p>
            @endif
            <div class="form-actions"><button class="button" type="submit">{{ $user->exists ? 'Save changes' : 'Create user' }}</button><a class="text-link" href="{{ route('users.index') }}">Cancel</a></div>
        </form>
    </section>
@endsection
