<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('account.password');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Auth::logoutOtherDevices($validated['current_password']);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        $request->session()->regenerate();

        return redirect()
            ->route('account.password.edit')
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
