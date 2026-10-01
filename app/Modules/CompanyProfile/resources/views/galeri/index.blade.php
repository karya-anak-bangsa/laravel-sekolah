@extends('layouts.admin')

@section('title', 'Galeri Foto')
@section('pretitle', 'Situs')

@section('actions')
    @can('create', App\Modules\CompanyProfile\Models\Galeri::class)
        <a href="{{ route('admin.galeri.create') }}" class="btn btn-primary">Tambah Foto</a>
    @endcan
@endsection

@section('content')
    <x-admin.card flush>
        <x-admin.table :empty="$galeri->isEmpty()" empty-text="Belum ada foto di galeri.">
            <x-slot:head>
                <tr>
                    <th style="width:110px">Foto</th>
                    <th>Judul</th>
                    <th>Ditambahkan</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($galeri as $item)
                <tr>
                    <td><img src="{{ $item->urlGambarKecil() }}" alt="" loading="lazy" style="width:96px;height:64px;object-fit:cover;border-radius:6px;display:block"></td>
                    <td class="cell-strong">{{ $item->judul }}</td>
                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                    <td style="text-align:right">
                        @can('update', $item)
                            <a href="{{ route('admin.galeri.edit', $item) }}" class="btn btn-outline btn-sm">Ubah</a>
                        @endcan
                        @can('delete', $item)
                            <x-admin.delete-form :action="route('admin.galeri.destroy', $item)" message="Hapus foto ini dari galeri?" />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
        {{ $galeri->links('vendor.pagination.gentelella') }}
    </x-admin.card>
@endsection
