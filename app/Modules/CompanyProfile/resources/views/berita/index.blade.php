@extends('layouts.admin')

@section('title', 'Berita & Pengumuman')
@section('pretitle', 'Situs')

@section('actions')
    @can('create', App\Modules\CompanyProfile\Models\Berita::class)
        <a href="{{ route('admin.berita.create') }}" class="btn btn-primary">Tulis Berita</a>
    @endcan
@endsection

@section('content')
    <x-admin.card>
        <form method="GET" action="{{ route('admin.berita.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div style="min-width:220px">
                <label class="form-label" for="f_q">Cari judul</label>
                <input class="form-control" id="f_q" type="search" name="q" value="{{ request('q') }}">
            </div>
            <div style="min-width:150px">
                <label class="form-label" for="f_jenis">Jenis</label>
                <select class="form-control" id="f_jenis" name="jenis">
                    <option value="">Semua</option>
                    @foreach ($jenis as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:150px">
                <label class="form-label" for="f_status">Status</label>
                <select class="form-control" id="f_status" name="status">
                    <option value="">Semua</option>
                    @foreach ($status as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </form>
    </x-admin.card>

    <x-admin.card flush style="margin-top:16px">
        <x-admin.table :empty="$berita->isEmpty()" empty-text="Belum ada berita atau pengumuman.">
            <x-slot:head>
                <tr>
                    <th>Judul</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th>Tanggal Terbit</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($berita as $item)
                <tr>
                    <td class="cell-strong">{{ $item->judul }}</td>
                    <td><span class="chip">{{ $item->jenis->label() }}</span></td>
                    <td>
                        @if ($item->sudahTayang())
                            <x-admin.status type="green">Tayang</x-admin.status>
                        @elseif ($item->status === App\Modules\CompanyProfile\Enums\StatusBerita::Terbit)
                            <x-admin.status type="blue">Terjadwal</x-admin.status>
                        @else
                            <x-admin.status type="yellow">Draf</x-admin.status>
                        @endif
                    </td>
                    <td>{{ $item->tanggal_terbit?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td style="text-align:right">
                        @if ($item->sudahTayang())
                            <a href="{{ route('berita.show', $item->slug) }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Lihat</a>
                        @endif
                        @can('update', $item)
                            <a href="{{ route('admin.berita.edit', $item) }}" class="btn btn-outline btn-sm">Ubah</a>
                        @endcan
                        @can('delete', $item)
                            <x-admin.delete-form :action="route('admin.berita.destroy', $item)" message="Hapus berita atau pengumuman ini? Tindakan ini tidak dapat dibatalkan." />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
        {{ $berita->links('vendor.pagination.gentelella') }}
    </x-admin.card>
@endsection
