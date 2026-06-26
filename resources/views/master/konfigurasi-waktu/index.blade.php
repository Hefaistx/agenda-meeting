@extends('layouts.app')

@section('title', 'Konfigurasi Waktu Meeting')
@section('breadcrumb', 'Master > Konfigurasi Waktu')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 style="font-size:15px;font-weight:700;color:#231f20;margin:0">
        <i class="bi bi-clock me-2" style="color:var(--teal)"></i>Konfigurasi Waktu Meeting
    </h4>
    @if($canEdit)
    <a href="{{ route('konfigurasi-waktu.create') }}" class="btn btn-teal btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Konfigurasi
    </a>
    @endif
</div>

{{-- Search --}}
<form method="GET" action="{{ route('konfigurasi-waktu.index') }}" class="mb-3">
    <div class="d-flex gap-2" style="max-width:360px">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Cari kategori..."
               value="{{ $search }}">
        <button type="submit" class="btn btn-sm btn-teal px-3">
            <i class="bi bi-search"></i>
        </button>
        @if($search)
        <a href="{{ route('konfigurasi-waktu.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x"></i>
        </a>
        @endif
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width:42px">No</th>
                        <th>Kategori</th>
                        <th>Waktu Mulai Minimal</th>
                        <th>Waktu Selesai Maksimal</th>
                        @if($canEdit)
                        <th style="width:90px" class="text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <span class="badge"
                                  style="background:{{ \App\Models\Meeting::$kategoriColors[$item->kategori] ?? '#6b7280' }};
                                         font-size:12px;font-weight:600;padding:4px 10px">
                                {{ $item->kategori }}
                            </span>
                        </td>
                        <td>
                            @if($item->waktu_mulai_min)
                                <span style="font-weight:600;color:#1e2847">
                                    <i class="bi bi-clock me-1" style="color:var(--teal)"></i>
                                    {{ \Illuminate\Support\Str::substr($item->waktu_mulai_min, 0, 5) }}
                                </span>
                            @else
                                <span style="color:#9ca3af;font-style:italic">Tidak ada batasan</span>
                            @endif
                        </td>
                        <td>
                            @if($item->waktu_selesai_max)
                                <span style="font-weight:600;color:#1e2847">
                                    <i class="bi bi-clock me-1" style="color:var(--teal)"></i>
                                    {{ \Illuminate\Support\Str::substr($item->waktu_selesai_max, 0, 5) }}
                                </span>
                            @else
                                <span style="color:#9ca3af;font-style:italic">Tidak ada batasan</span>
                            @endif
                        </td>
                        @if($canEdit)
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('konfigurasi-waktu.edit', $item) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   style="font-size:11px;padding:3px 7px"
                                   data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('konfigurasi-waktu.destroy', $item) }}"
                                      onsubmit="return confirm('Hapus konfigurasi kategori {{ $item->kategori }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            style="font-size:11px;padding:3px 7px"
                                            data-bs-toggle="tooltip" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canEdit ? 5 : 4 }}" class="text-center py-5" style="color:#9ca3af">
                            <i class="bi bi-clock" style="font-size:28px;display:block;margin-bottom:8px"></i>
                            {{ $search ? 'Tidak ada konfigurasi yang cocok dengan pencarian.' : 'Belum ada data konfigurasi waktu.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el))
</script>
@endpush
