<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Actions\BuatAkunPegawai;
use App\Modules\Core\Actions\ResetPasswordPengguna;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Http\Requests\PenggunaRequest;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Models\Pegawai;
use App\Support\AksiDitolak;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Akun pegawai (login /admin). Akun pendaftar PPDB dikelola di modul PPDB. */
class PenggunaController
{
    private const PER_HALAMAN = 25;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $pengguna = User::withTrashed()
            ->with(['pegawai.unitSekolah', 'roles'])
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', Role::Pendaftar->value))
            // Pengguna tingkat unit: hanya akun pegawai di unitnya (Pegawai sudah ber-scope unit).
            ->when($request->user()->idUnitSekolah() !== null, fn ($q) => $q->whereHas('pegawai'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = '%'.addcslashes((string) $request->input('q'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $kata)->orWhere('username', 'like', $kata));
            })
            ->when($request->filled('role'), fn ($q) => $q->role($request->input('role')))
            ->orderBy('nama')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('core::pengguna.index', ['pengguna' => $pengguna, 'roles' => $this->daftarRole()]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('core::pengguna.form', [
            'pengguna' => new User,
            'pegawaiTanpaAkun' => $this->pegawaiTanpaAkun(),
            'roles' => $this->daftarRole(),
        ]);
    }

    public function store(PenggunaRequest $request, BuatAkunPegawai $buat): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $pegawai = Pegawai::findOrFail($request->validated('id_pegawai'));
        $roles = Gate::allows('assignRole', User::class) ? ($request->validated('roles') ?? []) : [];

        $hasil = $buat($pegawai, $request->validated('username'), $roles);

        return redirect()->route('admin.pengguna.index')->with('akun_baru', [
            'nama' => $hasil['user']->nama,
            'username' => $hasil['user']->username,
            'password' => $hasil['password'],
        ]);
    }

    public function edit(User $pengguna): View
    {
        Gate::authorize('update', $pengguna);

        return view('core::pengguna.form', [
            'pengguna' => $pengguna->load('roles', 'pegawai.unitSekolah'),
            'pegawaiTanpaAkun' => collect(),
            'roles' => $this->daftarRole(),
        ]);
    }

    public function update(PenggunaRequest $request, User $pengguna): RedirectResponse
    {
        Gate::authorize('update', $pengguna);

        $pengguna->update(['username' => $request->validated('username')]);

        if (Gate::allows('assignRole', User::class)) {
            $roles = $request->validated('roles') ?? [];

            if ($pengguna->is($request->user()) && $pengguna->hasRole(Role::SuperAdmin->value) && ! in_array(Role::SuperAdmin->value, $roles, true)) {
                throw new AksiDitolak('Anda tidak dapat mencabut role Super Administrator dari akun Anda sendiri.');
            }

            $pengguna->syncRoles($roles);
        }

        return redirect()->route('admin.pengguna.index')->with('status', 'Akun berhasil diperbarui.');
    }

    public function resetPassword(User $pengguna, ResetPasswordPengguna $reset): RedirectResponse
    {
        Gate::authorize('resetPassword', $pengguna);

        $password = $reset($pengguna);

        return redirect()->route('admin.pengguna.index')->with('akun_baru', [
            'nama' => $pengguna->nama,
            'username' => $pengguna->username,
            'password' => $password,
        ]);
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        Gate::authorize('delete', $pengguna);

        if ($pengguna->is($request->user())) {
            throw new AksiDitolak('Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $pengguna->delete();

        return redirect()->route('admin.pengguna.index')->with('status', "Akun {$pengguna->username} dinonaktifkan.");
    }

    public function pulihkan(User $pengguna): RedirectResponse
    {
        Gate::authorize('pulihkan', $pengguna);

        $pengguna->restore();

        return redirect()->route('admin.pengguna.index')->with('status', "Akun {$pengguna->username} diaktifkan kembali.");
    }

    private function pegawaiTanpaAkun()
    {
        return Pegawai::query()
            ->with('unitSekolah')
            ->whereDoesntHave('user', fn ($q) => $q->withTrashed())
            ->orderBy('nama_pegawai')
            ->get();
    }

    /** @return list<Role> role yang dapat dimiliki akun pegawai */
    private function daftarRole(): array
    {
        return array_values(array_filter(Role::cases(), fn (Role $r) => $r !== Role::Pendaftar));
    }
}
