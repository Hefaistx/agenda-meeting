@extends('layouts.app')

@section('title', 'Edit Konfigurasi Waktu – ' . $konfigurasi->kategori)
@section('breadcrumb', 'Master > Konfigurasi Waktu > Edit')

@section('content')

<div class="mb-3">
    <a href="{{ route('konfigurasi-waktu.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body pt-3">
        <form method="POST" action="{{ route('konfigurasi-waktu.update', $konfigurasi) }}">
            @csrf @method('PUT')
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="kategori"
                           class="form-control @error('kategori') is-invalid @enderror"
                           value="{{ old('kategori', $konfigurasi->kategori) }}">
                    @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Waktu Mulai Minimal</label>
                    <input type="time" name="waktu_mulai_min"
                           class="form-control @error('waktu_mulai_min') is-invalid @enderror"
                           value="{{ old('waktu_mulai_min', $konfigurasi->waktu_mulai_min ? \Illuminate\Support\Str::substr($konfigurasi->waktu_mulai_min, 0, 5) : '') }}">
                    <div class="form-text">Meeting tidak boleh dimulai sebelum jam ini. Kosongkan jika tidak ada batasan.</div>
                    @error('waktu_mulai_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Waktu Selesai Maksimal</label>
                    <input type="time" name="waktu_selesai_max"
                           class="form-control @error('waktu_selesai_max') is-invalid @enderror"
                           value="{{ old('waktu_selesai_max', $konfigurasi->waktu_selesai_max ? \Illuminate\Support\Str::substr($konfigurasi->waktu_selesai_max, 0, 5) : '') }}">
                    <div class="form-text">Meeting harus selesai sebelum atau tepat jam ini. Kosongkan jika tidak ada batasan.</div>
                    @error('waktu_selesai_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <small class="text-muted">
                        Dibuat: {{ $konfigurasi->created_at->format('d-m-Y H:i') }}
                        &nbsp;·&nbsp;
                        Diperbarui: {{ $konfigurasi->updated_at->format('d-m-Y H:i') }}
                    </small>
                </div>

                <div class="col-12 d-flex gap-2 pt-2" style="border-top:1px solid #e2e8f0;margin-top:4px">
                    <button type="submit" class="btn btn-sm px-4"
                            style="background:#3b5bdb;border-color:#3b5bdb;color:#fff;font-weight:600">
                        Simpan
                    </button>
                    <a href="{{ route('konfigurasi-waktu.index') }}" class="btn btn-sm px-4"
                       style="background:#e03131;border-color:#e03131;color:#fff;font-weight:600">
                        Batal
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
