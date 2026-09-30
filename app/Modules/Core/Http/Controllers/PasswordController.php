<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Http\Requests\GantiPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/** Pengguna mengganti kata sandinya sendiri. */
class PasswordController
{
    public function edit(): View
    {
        return view('core::password.edit');
    }

    public function update(GantiPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
            'wajib_ganti_password' => false,
        ]);

        return redirect()->route('admin.dashboard')->with('status', 'Kata sandi berhasil diganti.');
    }
}
