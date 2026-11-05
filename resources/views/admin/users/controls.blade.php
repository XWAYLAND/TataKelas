@php($isCurrentUser = $user->is(auth()->user()))

<div class="{{ $compact ? 'space-y-3' : 'space-y-2' }}">
    @if($isCurrentUser)
        <div>
            <span class="badge badge-primary badge-outline">{{ ucfirst($user->role) }}</span>
            <p class="mt-1 text-xs text-base-content/50">Role akun aktif dilindungi.</p>
        </div>
    @else
        <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <label class="sr-only" for="role-{{ $layout }}-{{ $user->id }}">Role untuk {{ $user->name }}</label>
            <select
                id="role-{{ $layout }}-{{ $user->id }}"
                name="role"
                class="select select-bordered select-sm {{ $compact ? 'min-w-0 flex-1' : 'w-32' }}"
            >
                @foreach(['admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student'] as $value => $label)
                    <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm {{ $compact ? '' : 'shrink-0' }}">Simpan</button>
        </form>
    @endif

    <div class="{{ $compact ? 'grid grid-cols-2 gap-2' : 'flex flex-wrap items-center gap-2' }}">
        <form
            method="POST"
            action="{{ route('admin.users.reset-password', $user) }}"
            class="{{ $compact ? 'min-w-0' : '' }}"
            onsubmit="return confirm('Buat password sementara baru? Password lama tidak akan berlaku lagi.');"
        >
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm w-full border-base-300">
                <i class="fas fa-key"></i>
                Reset password
            </button>
        </form>
        @unless($isCurrentUser)
            <form
                method="POST"
                action="{{ route('admin.users.destroy', $user) }}"
                class="{{ $compact ? 'min-w-0' : '' }}"
                onsubmit="return confirm('Hapus akun ini secara permanen? Akun yang masih memiliki data terkait akan ditolak.');"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm w-full text-error">
                    <i class="fas fa-trash"></i>
                    Hapus
                </button>
            </form>
        @else
            <a href="{{ route('account.password.edit') }}" class="btn btn-ghost btn-sm w-full border-base-300">
                Ganti password
            </a>
        @endunless
    </div>
</div>
