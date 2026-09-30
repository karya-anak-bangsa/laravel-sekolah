@extends('layouts.admin')

@section('title', 'Pegawai')
@section('pretitle', 'Master Data')

@section('actions')
    @can('create', App\Modules\Kepegawaian\Models\Pegawai::class)
        <a href="{{ route('admin.pegawai.create') }}" class="btn btn-primary">Tambah Pegawai</a>
    @endcan
@endsection

@section('content')
    <x-admin.card>
        <form method="GET" action="{{ route('admin.pegawai.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div style="min-width:220px">
                <label class="form-label" for="f_q">Cari nama</label>
                <input class="form-control" id="f_q" type="search" name="q" value="{{ request('q') }}" placeholder="Nama pegawai">
            </div>
            @if ($units->count() > 1)
                <div style="min-width:160px">
                    <label class="form-label" for="f_unit">Unit</label>
                    <select class="form-control" id="f_unit" name="id_unit_sekolah">
                        <option value="">Semua</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id_unit_sekolah }}" @selected(request('id_unit_sekolah') == $unit->id_unit_sekolah)>{{ $unit->nama_unit_sekolah }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div style="min-width:160px">
                <label class="form-label" for="f_jenis">Jenis</label>
                <select class="form-control" id="f_jenis" name="jenis_pegawai">
                    <option value="">Semua</option>
                    @foreach ($jenis as $item)
                        <option value="{{ $item->value }}" @selected(request('jenis_pegawai') === $item->value)>{{ $item->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </form>
    </x-admin.card>

    <x-admin.card flush style="margin-top:16px">
        <x-admin.table :empty="$pegawai->isEmpty()" empty-text="Tidak ada pegawai yang sesuai filter.">
            <x-slot:head>
                <tr>
                    <th>Nama</th>
                    <th>Unit</th>
                    <th>Jenis</th>
                    <th>Akun</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($pegawai as $item)
                <tr>
                    <td class="cell-strong">{{ $item->nama_pegawai }}</td>
                    <td>{{ $item->unitSekolah?->jenjang->label() ?? 'Yayasan' }}</td>
                    <td><span class="chip">{{ $item->jenis_pegawai->label() }}</span></td>
                    <td>
                        @if ($item->user)
                            {{ $item->user->username }}
                            @if ($item->user->trashed())
                                <x-admin.status type="red">Nonaktif</x-admin.status>
                            @endif
                        @else
                            <span style="color:var(--text-muted)">Belum ada akun</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @can('update', $item)
                            <a href="{{ route('admin.pegawai.edit', $item) }}" class="btn btn-outline btn-sm">Ubah</a>
                        @endcan
                        @can('delete', $item)
                            <x-admin.delete-form :action="route('admin.pegawai.destroy', $item)" :message="'Hapus pegawai '.$item->nama_pegawai.'? Akun loginnya ikut dinonaktifkan.'" />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
        {{ $pegawai->links('vendor.pagination.gentelella') }}
    </x-admin.card>
@endsection
