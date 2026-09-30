@extends('layouts.admin')

@section('title', 'Tahun Ajaran')
@section('pretitle', 'Master Data')

@section('actions')
    @can('create', App\Modules\Core\Models\TahunAjaran::class)
        <a href="{{ route('admin.tahun-ajaran.create') }}" class="btn btn-primary">Tambah Tahun Ajaran</a>
    @endcan
@endsection

@section('content')
    <x-admin.card flush>
        <x-admin.table :empty="$tahunAjaran->isEmpty()" empty-text="Belum ada tahun ajaran.">
            <x-slot:head>
                <tr>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($tahunAjaran as $item)
                <tr>
                    <td class="cell-strong">{{ $item->nama_tahun_ajaran }}</td>
                    <td>{{ $item->semester_aktif->label() }}</td>
                    <td>
                        @if ($item->is_aktif)
                            <x-admin.status type="green">Aktif</x-admin.status>
                        @else
                            <x-admin.status type="yellow">Tidak aktif</x-admin.status>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @can('update', $item)
                            @unless ($item->is_aktif)
                                <form method="POST" action="{{ route('admin.tahun-ajaran.aktifkan', $item) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Aktifkan</button>
                                </form>
                            @endunless
                            <a href="{{ route('admin.tahun-ajaran.edit', $item) }}" class="btn btn-outline btn-sm">Ubah</a>
                        @endcan
                        @can('delete', $item)
                            <x-admin.delete-form :action="route('admin.tahun-ajaran.destroy', $item)" :message="'Hapus tahun ajaran '.$item->nama_tahun_ajaran.'?'" />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    </x-admin.card>
@endsection
