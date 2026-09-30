@extends('layouts.admin')

@section('title', 'Kelas')
@section('pretitle', 'Master Data')

@section('actions')
    @can('create', App\Modules\Core\Models\Kelas::class)
        <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary">Tambah Kelas</a>
    @endcan
@endsection

@section('content')
    <x-admin.card>
        <form method="GET" action="{{ route('admin.kelas.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div style="min-width:180px">
                <label class="form-label" for="f_tahun">Tahun ajaran</label>
                <select class="form-control" id="f_tahun" name="id_tahun_ajaran">
                    <option value="">Semua</option>
                    @foreach ($tahunAjaran as $item)
                        <option value="{{ $item->id_tahun_ajaran }}" @selected((string) $idTahun === (string) $item->id_tahun_ajaran)>{{ $item->nama_tahun_ajaran }}{{ $item->is_aktif ? ' (aktif)' : '' }}</option>
                    @endforeach
                </select>
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
            <div style="min-width:120px">
                <label class="form-label" for="f_tingkat">Tingkat</label>
                <select class="form-control" id="f_tingkat" name="tingkat">
                    <option value="">Semua</option>
                    @foreach (range(7, 12) as $tingkat)
                        <option value="{{ $tingkat }}" @selected(request('tingkat') == $tingkat)>{{ $tingkat }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </form>
    </x-admin.card>

    <x-admin.card flush style="margin-top:16px">
        <x-admin.table :empty="$kelas->isEmpty()" empty-text="Tidak ada kelas yang sesuai filter.">
            <x-slot:head>
                <tr>
                    <th>Kelas</th>
                    <th>Unit</th>
                    <th>Jurusan</th>
                    <th>Tingkat</th>
                    <th>Tahun Ajaran</th>
                    <th>Wali Kelas</th>
                    <th>Siswa</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($kelas as $item)
                <tr>
                    <td class="cell-strong">{{ $item->nama_kelas }}</td>
                    <td>{{ $item->unitSekolah->jenjang->label() }}</td>
                    <td>{{ $item->jurusan?->kode_jurusan ?? '—' }}</td>
                    <td>{{ $item->tingkat }}</td>
                    <td>{{ $item->tahunAjaran->nama_tahun_ajaran }}</td>
                    <td>{{ $item->waliKelas?->nama_pegawai ?? '—' }}</td>
                    <td>{{ $item->anggota_kelas_count }}</td>
                    <td style="text-align:right">
                        @can('viewAnggota', $item)
                            <a href="{{ route('admin.kelas.anggota', $item) }}" class="btn btn-outline btn-sm">Anggota</a>
                        @endcan
                        @can('update', $item)
                            <a href="{{ route('admin.kelas.edit', $item) }}" class="btn btn-outline btn-sm">Ubah</a>
                        @endcan
                        @can('delete', $item)
                            <x-admin.delete-form :action="route('admin.kelas.destroy', $item)" :message="'Hapus kelas '.$item->nama_kelas.'?'" />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
        {{ $kelas->links('vendor.pagination.gentelella') }}
    </x-admin.card>
@endsection
