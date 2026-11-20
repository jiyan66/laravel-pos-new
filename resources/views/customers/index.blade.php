@extends('layouts.app')
@section('title', 'Customer Accounts')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">CUSTOMER DIRECTORY</p><h1>Customer Accounts</h1><p class="subtitle">Your customer contact details, together.</p></div></div>
    <section class="panel" aria-labelledby="customers-heading">
        <div class="panel-heading"><h2 id="customers-heading">All customers</h2><span class="count">{{ $customers->count() }} total</span></div>
        @if ($customers->isEmpty())
            <div class="empty-state">No customer records are available.</div>
        @else
            <div class="table-scroll" role="region" aria-label="Customer records" tabindex="0"><table><caption class="sr-only">Customer account records</caption><thead><tr><th scope="col">ID</th><th scope="col">Full name</th><th scope="col">Email</th><th scope="col">Phone</th></tr></thead><tbody>
                @foreach ($customers as $customer)
                    <tr><td class="muted">{{ $customer->id }}</td><td class="record-name">{{ $customer->full_name }}</td><td>{{ $customer->email }}</td><td>{{ $customer->phone ?? 'Not provided' }}</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
@endsection
