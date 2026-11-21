@extends('layouts.app')
@section('title', 'Customer Details')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">CUSTOMER ACCOUNT</p><h1>{{ $customer->full_name }}</h1><p class="subtitle">Customer details</p></div><a class="button button-light" href="{{ route('customers.index') }}">Back to customers</a></div>
    <section class="panel about-panel">
        <dl class="profile-details">
            <div><dt>ID</dt><dd>{{ $customer->id }}</dd></div>
            <div><dt>Full name</dt><dd>{{ $customer->full_name }}</dd></div>
            <div><dt>Email</dt><dd>{{ $customer->email }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $customer->phone ?? 'Not provided' }}</dd></div>
        </dl>
        <div class="form-actions"><a class="button" href="{{ route('customers.edit', $customer) }}">Edit customer</a></div>
        <details class="delete-panel"><summary>Delete this customer</summary><p>This permanently removes the customer record.</p><form method="POST" action="{{ route('customers.destroy', $customer) }}">@csrf @method('DELETE')<button class="button button-danger" type="submit">Confirm delete customer</button></form></details>
    </section>
@endsection
