@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
    <div class="app-surface p-6 sm:p-8">
        <div class="mb-6">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">Get started</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight">Create your account</h1>
            <p class="mt-2 text-sm text-base-content/55">Your folders, notes, and photos stay private to you.</p>
        </div>

        @if($errors->any())
            <div role="alert" class="alert alert-error mb-5 items-start">
                <i class="fas fa-circle-exclamation mt-0.5"></i>
                <div class="text-sm">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-medium">Name</label>
                <label class="input input-bordered flex w-full items-center gap-2">
                    <i class="fas fa-user text-xs text-base-content/40"></i>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="grow" placeholder="Your name">
                </label>
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                <label class="input input-bordered flex w-full items-center gap-2">
                    <i class="fas fa-envelope text-xs text-base-content/40"></i>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="grow" placeholder="you@example.com">
                </label>
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                <label class="input input-bordered flex w-full items-center gap-2">
                    <i class="fas fa-lock text-xs text-base-content/40"></i>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="grow" placeholder="At least 8 characters">
                </label>
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium">Confirm password</label>
                <label class="input input-bordered flex w-full items-center gap-2">
                    <i class="fas fa-lock text-xs text-base-content/40"></i>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="grow" placeholder="Repeat your password">
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-full">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-base-content/55">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Sign in</a>
        </p>
    </div>
@endsection
