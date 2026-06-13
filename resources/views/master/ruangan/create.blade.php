@extends('layouts.app')

@section('title', 'Tambah Ruangan')
@section('breadcrumb', 'Master > Ruangan > Tambah')

@section('content')

<div class="mb-3">
    <a href="{{ route('ruangan.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body pt-3">
        <form method="POST" action="{{ route('ruangan.store') }}">
            @csrf
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           placeholder="Contoh: Ruang Rapat A"
                           value="{{ old('nama') }}" autofocus>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
