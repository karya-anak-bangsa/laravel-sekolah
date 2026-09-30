@extends('layouts.admin')

@section('title', 'Pengguna & Role')
@section('pretitle', 'Sistem')

@section('actions')
    @can('create', App\Modules\Core\Models\User::class)
        <a href="{{ route('admin.pengguna.create') }}" class="btn btn-primary">Buat Akun Pegawai</a>
    @endcan
@endsection

@section('content')
    @if (session('akun_baru'))
        @php($akun = session('akun_baru'))
        <x-admin.alert type="warning" class="flash">
            <strong>Kata sandi sementara untuk {{ $akun['nama'] }}</strong><br>
            Username: <code>{{ $akun['username'] }}</code> &nbsp; Kata sandi: <code style="font-size:15px;font-weight:600">{{ $akun['password'] }}</code><br>
            Catat dan sampaikan langsung kepada yang bersangkutan. Kata sandi ini hanya tampil sekali dan wajib diganti saat login pertama.
        </x-admin.alert>
    @endif

    <x-admin.card>
        <form method="GET" action="{{ route('admin.pengguna.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div style="min-width:220px">
                <label class="form-label" for="f_q">Cari</label>
                <input class="form-control" id="f_q" type="search" name="q" value="{{ request('q') }}" placeholder="Nama atau username">
            </div>
            <div style="min-width:200px">
                <label class="form-label" for="f_role">Role</label>
                <select class="form-control" id="f_role" name="role">
                    <option value="">Semua</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </form>
    </x-admin.card>

    <x-admin.card flush style="margin-top:16px">
        <x-admin.table :empty="$pengguna->isEmpty()" empty-text="Tidak ada akun yang sesuai filter.">
            <x-slot:head>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Unit</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($pengguna as $item)
                <tr>
                    <td class="cell-strong">{{ $item->nama }}</td>
                    <td>{{ $item->username }}</td>
                    <td>{{ $item->pegawai ? ($item->pegawai->unitSekolah?->jenjang->label() ?? 'Yayasan') : '—' }}</td>
                    <td>
                        @forelse ($item->roles as $role)
                            <span class="chip">{{ App\Modules\Core\Enums\Role::tryFrom($role->name)?->label() ?? $role->name }}</span>
                        @empty
                            <span style="color:var(--text-muted)">Belum ada role</span>
                        @endforelse
                    </td>
                    <td>
                        @if ($item->trashed())
                            <x-admin.status type="red">Nonaktif</x-admin.status>
                        @elseif ($item->wajib_ganti_password)
                            <x-admin.status type="yellow">Menunggu ganti sandi</x-admin.status>
                        @else
                            <x-admin.status type="green">Aktif</x-admin.status>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @if ($item->trashed())
                            @can('pulihkan', $item)
                                <form method="POST" action="{{ route('admin.pengguna.pulihkan', $item) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Aktifkan</button>
                                </form>
                            @endcan
                        @else
                            @can('update', $item)
                                <a href="{{ route('admin.pengguna.edit', $item) }}" class="btn btn-outline btn-sm">Ubah</a>
                            @endcan
                            @can('resetPassword', $item)
                                <form method="POST" action="{{ route('admin.pengguna.reset-password', $item) }}" style="display:inline" onsubmit="return confirm('Reset kata sandi {{ $item->nama }}? Kata sandi lama tidak berlaku lagi.')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Reset sandi</button>
                                </form>
                            @endcan
                            @can('delete', $item)
                                <x-admin.delete-form :action="route('admin.pengguna.destroy', $item)" label="Nonaktifkan" :message="'Nonaktifkan akun '.$item->username.'?'" />
                            @endcan
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
        {{ $pengguna->links('vendor.pagination.gentelella') }}
    </x-admin.card>
@endsection
