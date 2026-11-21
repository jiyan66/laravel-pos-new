@extends('layouts.app')
@section('title', 'User Accounts')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">USER DIRECTORY</p><h1>User Accounts</h1><p class="subtitle">The people in your account workspace.</p></div><a class="button" href="{{ route('users.create') }}">Create user</a></div>
    <section class="panel" aria-labelledby="users-heading">
        <div class="panel-heading"><h2 id="users-heading">All users</h2><span class="count">{{ $users->count() }} total</span></div>
        @if ($users->isEmpty())
            <div class="empty-state">No user records are available.</div>
        @else
            <div class="table-scroll" role="region" aria-label="User records" tabindex="0"><table><caption class="sr-only">User account records</caption><thead><tr><th scope="col">ID</th><th scope="col">Avatar</th><th scope="col">Username</th><th scope="col">Full name</th><th scope="col">Actions</th></tr></thead><tbody>
                @foreach ($users as $user)
                    <tr><td class="muted">{{ $user->id }}</td><td><img class="avatar-thumb" src="{{ $user->avatar_url }}" alt="{{ $user->full_name }} avatar" width="48" height="48" loading="lazy"></td><td>{{ $user->username }}</td><td class="record-name">{{ $user->full_name }}</td><td class="row-actions"><a class="text-link" href="{{ route('users.show', $user) }}">View</a><a class="text-link" href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->full_name }}">Edit</a></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
@endsection
