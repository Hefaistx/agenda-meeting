@extends('layouts.app')

@section('title', 'Edit Ruangan')
@section('breadcrumb', 'Master > Ruangan > Edit')

@section('content')

<div class="page-hdr">
    <h4><i class="bi bi-door-open me-2" style="color:var(--teal)"></i>Edit Ruangan</h4>
    <a href="{{ route('ruangan.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body pt-3">
        <form method="POST" action="{{ route('ruangan.update', $ruangan) }}">
            @csrf @method('PUT')
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama', $ruangan->nama) }}">
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <small class="text-muted">
                        Dibuat: {{ $ruangan->created_at->format('d-m-Y H:i') }}
                        &nbsp;·&nbsp;
                        Diperbarui: {{ $ruangan->updated_at->format('d-m-Y H:i') }}
                    </small>
                </div>

                <div class="col-12 d-flex gap-2 pt-2" style="border-top:1px solid #e2e8f0;margin-top:4px">
                    <button type="submit" class="btn btn-sm px-4"
                            style="background:#3b5bdb;border-color:#3b5bdb;color:#fff;font-weight:600">
                        Submit
                    </button>
                    <a href="{{ route('ruangan.index') }}" class="btn btn-sm px-4"
                       style="background:#e03131;border-color:#e03131;color:#fff;font-weight:600">
                        Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
