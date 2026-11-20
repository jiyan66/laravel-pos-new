@extends('layouts.app')
@section('title', 'User Accounts')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">USER DIRECTORY</p><h1>User Accounts</h1><p class="subtitle">The people in your account workspace.</p></div></div>
    <section class="panel" aria-labelledby="users-heading">
        <div class="panel-heading"><h2 id="users-heading">All users</h2><span class="count">{{ $users->count() }} total</span></div>
        @if ($users->isEmpty())
            <div class="empty-state">No user records are available.</div>
        @else
            <div class="table-scroll" role="region" aria-label="User records" tabindex="0"><table><caption class="sr-only">User account records</caption><thead><tr><th scope="col">ID</th><th scope="col">Username</th><th scope="col">Full name</th></tr></thead><tbody>
                @foreach ($users as $user)
                    <tr><td class="muted">{{ $user->id }}</td><td>{{ $user->username }}</td><td class="record-name">{{ $user->full_name }}</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
@endsection
