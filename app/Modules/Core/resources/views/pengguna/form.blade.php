@extends('layouts.admin')

@php($ada = $pengguna->exists)
@php($bolehRole = auth()->user()->can('assignRole', App\Modules\Core\Models\User::class))

@section('title', $ada ? 'Ubah Akun' : 'Buat Akun Pegawai')
@section('pretitle', 'Pengguna & Role')

@section('content')
    <x-admin.card style="max-width:640px">
        <form method="POST" action="{{ $ada ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            @if ($ada)
                <p><strong>{{ $pengguna->nama }}</strong></p>
            @else
                <x-form.select name="id_pegawai" label="Pegawai" required placeholder="— Pilih pegawai —"
                    :options="$pegawaiTanpaAkun->mapWithKeys(fn ($p) => [$p->id_pegawai => $p->nama_pegawai.' ('.($p->unitSekolah?->jenjang->label() ?? 'Yayasan').')'])->all()"
                    help="Hanya pegawai yang belum punya akun." />
            @endif

            <x-form.input name="username" label="Username" :value="$pengguna->username" :required="$ada"
                :help="$ada ? 'Huruf kecil, angka, titik, strip, garis bawah.' : 'Kosongkan untuk dibuat otomatis dari nama.'" />

            @if ($bolehRole)
                @php($dipilih = old('roles', $pengguna->roles->pluck('name')->all()))
                <div class="form-group">
                    <label class="form-label">Role</label>
                    @foreach ($roles as $role)
                        <label class="form-check" style="display:flex;gap:8px;margin-bottom:6px">
                            <input type="checkbox" name="roles[]" value="{{ $role->value }}" @checked(in_array($role->value, $dipilih, true))>
                            {{ $role->label() }}
                        </label>
                    @endforeach
                    <div class="form-help">Satu pegawai boleh memiliki lebih dari satu role.</div>
                </div>
            @else
                <p class="form-help">Role diatur oleh Super Administrator.</p>
            @endif

            @unless ($ada)
                <p class="form-help">Kata sandi sementara dibuat otomatis dan ditampilkan sekali setelah akun dibuat.</p>
            @endunless

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.pengguna.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
