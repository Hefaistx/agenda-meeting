@extends('layouts.app')

@section('title', 'Master Topik')
@section('breadcrumb', 'Master > Topik')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 style="font-size:15px;font-weight:700;color:#231f20;margin:0">
        <i class="bi bi-tags me-2" style="color:var(--teal)"></i>Master Topik
    </h4>
    @if($canEdit)
    <a href="{{ route('topik.create') }}" class="btn btn-teal btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Topik
    </a>
    @endif
</div>

{{-- Search --}}
<form method="GET" action="{{ route('topik.index') }}" class="mb-3">
    <div class="d-flex gap-2" style="max-width:360px">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Cari nama topik..."
               value="{{ $search }}">
        <button type="submit" class="btn btn-sm btn-teal px-3">
            <i class="bi bi-search"></i>
        </button>
        @if($search)
        <a href="{{ route('topik.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th>Nama Topik</th>
                        @if($canEdit)
                        <th style="width:90px" class="text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($topics as $i => $topik)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td style="font-weight:600;color:#1e2847">{{ $topik->nama }}</td>
                        @if($canEdit)
                        <td class="text-center">
                            <form method="POST" action="{{ route('topik.destroy', $topik) }}"
                                  onsubmit="return confirm('Hapus topik ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        style="font-size:11px;padding:3px 7px"
                                        data-bs-toggle="tooltip" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canEdit ? 3 : 2 }}" class="text-center py-5" style="color:#9ca3af">
                            <i class="bi bi-tags" style="font-size:28px;display:block;margin-bottom:8px"></i>
                            {{ $search ? 'Tidak ada topik yang cocok dengan pencarian.' : 'Belum ada data topik.' }}
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
