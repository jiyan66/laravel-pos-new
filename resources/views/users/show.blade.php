@extends('layouts.app')
@section('title', 'User Details')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">USER ACCOUNT</p><h1>{{ $user->full_name }}</h1><p class="subtitle">User account details</p></div><a class="button button-light" href="{{ route('users.index') }}">Back to users</a></div>
    <section class="panel about-panel">
        <img class="avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->full_name }} avatar" width="96" height="96">
        <dl class="profile-details">
            <div><dt>ID</dt><dd>{{ $user->id }}</dd></div>
            <div><dt>Username</dt><dd>{{ $user->username }}</dd></div>
            <div><dt>Full name</dt><dd>{{ $user->full_name }}</dd></div>
            <div><dt>Login email</dt><dd>{{ $user->email ?? 'Not configured yet' }}</dd></div>
        </dl>
        <div class="form-actions"><a class="button" href="{{ route('users.edit', $user) }}">Edit user</a></div>
        @if (! $user->is(auth()->user()))
            <details class="delete-panel"><summary>Delete this user</summary><p>This permanently removes the account and its prepared avatar.</p><form method="POST" action="{{ route('users.destroy', $user) }}">@csrf @method('DELETE')<button class="button button-danger" type="submit">Confirm delete user</button></form></details>
        @else
            <p class="field-help">You cannot delete the account you are currently using.</p>
        @endif
    </section>
@endsection
