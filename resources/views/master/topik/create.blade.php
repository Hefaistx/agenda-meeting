@extends('layouts.app')

@section('title', 'Tambah Topik')
@section('breadcrumb', 'Master > Topik > Tambah')

@section('content')

<div class="mb-3">
    <a href="{{ route('topik.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body pt-3">
        <form method="POST" action="{{ route('topik.store') }}">
            @csrf
            <div class="row g-3">

                <div class="col-md-8">
                    <label class="form-label">Nama Topik <span class="text-danger">*</span></label>
                    <input type="text" name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           placeholder="Contoh: Review PRD dan timeline"
                           value="{{ old('nama') }}" autofocus>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2 pt-2" style="border-top:1px solid #e2e8f0;margin-top:4px">
                    <button type="submit" class="btn btn-sm px-4"
                            style="background:#3b5bdb;border-color:#3b5bdb;color:#fff;font-weight:600">
                        Submit
                    </button>
                    <a href="{{ route('topik.index') }}" class="btn btn-sm px-4"
                       style="background:#e03131;border-color:#e03131;color:#fff;font-weight:600">
                        Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
