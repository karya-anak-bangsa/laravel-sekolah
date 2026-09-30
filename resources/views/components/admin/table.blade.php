{{-- Isi <thead> lewat slot "head"; baris <tbody> lewat slot default. Tampil pesan kosong bila $empty true. --}}
@props(['empty' => false, 'emptyText' => 'Belum ada data.'])
<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table']) }}>
        @isset($head)
            <thead>{{ $head }}</thead>
        @endisset
        <tbody>
            @if ($empty)
                <tr><td colspan="99" style="text-align:center;color:var(--text-muted);padding:24px">{{ $emptyText }}</td></tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>
