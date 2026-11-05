@extends('layouts.app')

@section('title', 'Ganti Kata Sandi')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-base-content sm:text-3xl">Ganti kata sandi</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-base-content/65">
                Perbarui kata sandi akun {{ config('app.name') }}. Konfirmasi dengan kata sandi saat ini untuk melindungi akunmu.
            </p>
        </div>

        <section class="app-surface overflow-hidden">
            <div class="border-b border-base-300/80 px-5 py-4 sm:px-7">
                <h2 class="font-semibold">Keamanan akun</h2>
                <p class="mt-1 text-sm text-base-content/60">Gunakan sedikitnya 8 karakter dan jangan gunakan ulang kata sandi lama.</p>
            </div>

            <form method="POST" action="{{ route('account.password.update') }}" class="space-y-6 p-5 sm:p-7">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-2 block text-sm font-medium">Kata sandi saat ini</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="input input-bordered w-full @error('current_password') input-error @enderror"
                        aria-describedby="@error('current_password') current-password-error @enderror"
                    >
                    @error('current_password')
                        <p id="current-password-error" class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium">Kata sandi baru</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        aria-describedby="password-hint @error('password') password-error @enderror"
                    >
                    <p id="password-hint" class="mt-2 text-xs text-base-content/55">Minimal 8 karakter.</p>
                    @error('password')
                        <p id="password-error" class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium">Konfirmasi kata sandi baru</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="input input-bordered w-full @error('password_confirmation') input-error @enderror"
                        aria-describedby="@error('password_confirmation') password-confirmation-error @enderror"
                    >
                    @error('password_confirmation')
                        <p id="password-confirmation-error" class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-base-300/80 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('folders.index') }}" class="btn btn-ghost">Kembali ke workspace</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key" aria-hidden="true"></i>
                        Perbarui kata sandi
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
