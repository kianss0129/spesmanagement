@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4 text-center">Beneficiary Registration</h1>

        <form method="POST" action="{{ route('register.beneficiary.store') }}">
            @csrf

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Full name</label>
                <input name="name" value="{{ old('name') }}" class="input" />
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" class="input" />
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input name="password" type="password" class="input" />
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input name="password_confirmation" type="password" class="input" />
            </div>

            <button class="btn-primary w-full mt-4">Register</button>

            <p class="text-sm text-center mt-3">
                Already have an account?
                <a href="{{ route('login') }}" class="text-blue-600 underline">Login</a>
            </p>
        </form>
    </div>
</div>
@endsection

<style>
.input { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-top:10px; }
.btn-primary { background:#2563eb; color:white; padding:10px; border-radius:6px; }
.error { color:red; font-size:12px; }
</style>
