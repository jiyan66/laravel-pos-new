@extends('layouts.app')
@section('title', 'Home')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">WELCOME TO YOUR WORKSPACE</p><h1>People, organized.</h1><p class="subtitle">Find customer details and user accounts in one place.</p></div></div>
    <div class="dashboard-grid">
        <section class="panel dashboard-card"><span class="card-number" aria-hidden="true">01</span><h2>Customer Accounts</h2><p>View customer names, email addresses, and contact numbers.</p><a class="button" href="{{ route('customers.index') }}">View customers &rarr;</a></section>
        <section class="panel dashboard-card"><span class="card-number" aria-hidden="true">02</span><h2>User Accounts</h2><p>Browse the people listed in your account directory.</p><a class="button button-light" href="{{ route('users.index') }}">View users &rarr;</a></section>
    </div>
@endsection
