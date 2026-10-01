@extends('layouts.admin')

@section('title', 'Prestasi')
@section('pretitle', 'Situs')

@section('actions')
    @can('create', App\Modules\CompanyProfile\Models\Prestasi::class)
        <a href="{{ route('admin.prestasi.create') }}" class="btn btn-primary">Tambah Prestasi</a>
    @endcan
@endsection

@section('content')
    <x-admin.card>
        <form method="GET" action="{{ route('admin.prestasi.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div style="min-width:220px">
                <label class="form-label" for="f_q">Cari</label>
                <input class="form-control" id="f_q" type="search" name="q" value="{{ request('q') }}" placeholder="Judul atau nama peraih">
            </div>
            <div style="min-width:160px">
                <label class="form-label" for="f_tingkat">Tingkat</label>
                <select class="form-control" id="f_tingkat" name="tingkat">
                    <option value="">Semua</option>
                    @foreach ($tingkat as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('tingkat') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:110px">
                <label class="form-label" for="f_tahun">Tahun</label>
                <input class="form-control" id="f_tahun" type="number" name="tahun" value="{{ request('tahun') }}" min="2000">
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </form>
    </x-admin.card>

    <x-admin.card flush style="margin-top:16px">
        <x-admin.table :empty="$prestasi->isEmpty()" empty-text="Belum ada prestasi.">
            <x-slot:head>
                <tr>
                    <th>Prestasi</th>
                    <th>Peraih</th>
                    <th>Tingkat</th>
                    <th>Tahun</th>
                    <th>Unit</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($prestasi as $item)
                <tr>
                    <td class="cell-strong">{{ $item->judul }}</td>
                    <td>{{ $item->nama_peraih }}</td>
                    <td><span class="chip">{{ $item->tingkat->label() }}</span></td>
                    <td>{{ $item->tahun }}</td>
                    <td>{{ $item->unitSekolah?->jenjang->label() ?? 'Yayasan' }}</td>
                    <td style="text-align:right">
                        @can('update', $item)
                            <a href="{{ route('admin.prestasi.edit', $item) }}" class="btn btn-outline btn-sm">Ubah</a>
                        @endcan
                        @can('delete', $item)
                            <x-admin.delete-form :action="route('admin.prestasi.destroy', $item)" message="Hapus prestasi ini?" />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
        {{ $prestasi->links('vendor.pagination.gentelella') }}
    </x-admin.card>
@endsection
