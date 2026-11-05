@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <div class="app-surface p-6 sm:p-8">
        <div class="mb-6">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">Welcome back</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight">Masuk ke {{ config('app.name') }}</h1>
            <p class="mt-2 text-sm text-base-content/55">Masuk untuk membuka workspace pribadi dan classroom-mu.</p>
        </div>

        @if($errors->any())
            <div role="alert" class="alert alert-error mb-5 items-start">
                <i class="fas fa-circle-exclamation mt-0.5"></i>
                <span class="text-sm">{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                <label class="input input-bordered flex w-full items-center gap-2">
                    <i class="fas fa-envelope text-xs text-base-content/40"></i>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="grow" placeholder="you@example.com">
                </label>
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                <label class="input input-bordered flex w-full items-center gap-2">
                    <i class="fas fa-lock text-xs text-base-content/40"></i>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="grow" placeholder="Your password">
                </label>
            </div>

            <label class="flex items-center gap-2 text-sm text-base-content/65">
                <input type="checkbox" name="remember" value="1" class="checkbox checkbox-sm checkbox-primary">
                Tetap masuk setelah browser ditutup
            </label>
            <p class="-mt-3 text-xs text-base-content/50">Gunakan hanya di perangkat pribadi.</p>

            <button type="submit" class="btn btn-primary w-full">
                <i class="fas fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-base-content/55">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">Create one</a>
        </p>
    </div>
@endsection
