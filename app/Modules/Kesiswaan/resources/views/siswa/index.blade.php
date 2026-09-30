@extends('layouts.admin')

@section('title', 'Siswa')
@section('pretitle', 'Master Data')

@section('content')
    <x-admin.card>
        <form method="GET" action="{{ route('admin.siswa.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div style="min-width:220px">
                <label class="form-label" for="f_q">Cari</label>
                <input class="form-control" id="f_q" type="search" name="q" value="{{ request('q') }}" placeholder="Nama atau NISN">
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
            <div style="min-width:180px">
                <label class="form-label" for="f_kelas">Kelas (tahun ajaran aktif)</label>
                <select class="form-control" id="f_kelas" name="id_kelas">
                    <option value="">Semua</option>
                    @foreach ($kelas as $item)
                        <option value="{{ $item->id_kelas }}" @selected(request('id_kelas') == $item->id_kelas)>{{ $item->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:140px">
                <label class="form-label" for="f_status">Status</label>
                <select class="form-control" id="f_status" name="status_siswa">
                    <option value="">Semua</option>
                    @foreach ($status as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('status_siswa') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </form>
    </x-admin.card>

    <x-admin.card flush style="margin-top:16px">
        <x-admin.table :empty="$siswa->isEmpty()" empty-text="Tidak ada siswa yang sesuai filter.">
            <x-slot:head>
                <tr>
                    <th>Nama</th>
                    <th>NISN</th>
                    <th>L/P</th>
                    <th>Unit</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($siswa as $item)
                @php($kelasAktif = $item->anggotaKelas->first(fn ($a) => $a->kelas?->tahunAjaran?->is_aktif)?->kelas)
                <tr>
                    <td class="cell-strong">{{ $item->nama_siswa }}</td>
                    <td>{{ $item->nisn ?? '—' }}</td>
                    <td>{{ $item->jenis_kelamin?->value ? strtoupper($item->jenis_kelamin->value) : '—' }}</td>
                    <td>{{ $item->unitSekolah->jenjang->label() }}</td>
                    <td>{{ $kelasAktif?->nama_kelas ?? '—' }}</td>
                    <td>
                        <x-admin.status :type="match ($item->status_siswa->value) { 'aktif' => 'green', 'calon' => 'blue', 'lulus' => 'yellow', default => 'red' }">{{ $item->status_siswa->label() }}</x-admin.status>
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.siswa.show', $item) }}" class="btn btn-outline btn-sm">Detail</a>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
        {{ $siswa->links('vendor.pagination.gentelella') }}
    </x-admin.card>
@endsection
