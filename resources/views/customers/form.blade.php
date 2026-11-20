@extends('layouts.app')
@section('title', $customer->exists ? 'Edit Customer' : 'Create Customer')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">CUSTOMER ACCOUNTS</p><h1>{{ $customer->exists ? 'Edit customer' : 'Create customer' }}</h1><p class="subtitle">Keep customer contact details up to date.</p></div><a class="button button-light" href="{{ route('customers.index') }}">Back to customers</a></div>
    <section class="panel form-panel" aria-label="Customer form">
        <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
            @csrf
            @if ($customer->exists) @method('PUT') @endif
            <p class="form-note">Fields marked * are required.</p>
            <div class="field"><label for="full_name">Full name *</label><input id="full_name" name="full_name" type="text" maxlength="100" required autocomplete="name" value="{{ old('full_name', $customer->full_name) }}" @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror>@error('full_name')<p class="field-error" id="full_name-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="email">Email address *</label><input id="email" name="email" type="email" maxlength="100" required autocomplete="email" value="{{ old('email', $customer->email) }}" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>@error('email')<p class="field-error" id="email-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" value="{{ old('phone', $customer->phone) }}" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>@error('phone')<p class="field-error" id="phone-error">{{ $message }}</p>@enderror</div>
            <div class="form-actions"><button class="button" type="submit">{{ $customer->exists ? 'Save changes' : 'Create customer' }}</button><a class="text-link" href="{{ route('customers.index') }}">Cancel</a></div>
        </form>
    </section>
@endsection
