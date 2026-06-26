@extends('layouts.app')

@section('title', 'Tambah Konfigurasi Waktu')
@section('breadcrumb', 'Master > Konfigurasi Waktu > Tambah')

@section('content')

<div class="mb-3">
    <a href="{{ route('konfigurasi-waktu.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body pt-3">
        @if(empty($available))
        <div class="alert alert-info d-flex align-items-center gap-2 mb-0" style="font-size:13px">
            <i class="bi bi-info-circle-fill"></i>
            Semua kategori meeting sudah memiliki konfigurasi waktu. Hapus salah satu untuk menambah ulang.
        </div>
        @else
        <form method="POST" action="{{ route('konfigurasi-waktu.store') }}">
            @csrf
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori" class="form-select @error('kategori') is-invalid @enderror">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($available as $kat)
                        <option value="{{ $kat }}" {{ old('kategori') === $kat ? 'selected' : '' }}>
                            {{ $kat }}
                        </option>
                        @endforeach
                    </select>
                    @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Waktu Mulai Minimal</label>
                    <input type="time" name="waktu_mulai_min"
                           class="form-control @error('waktu_mulai_min') is-invalid @enderror"
                           value="{{ old('waktu_mulai_min') }}">
                    <div class="form-text">Meeting tidak boleh dimulai sebelum jam ini. Kosongkan jika tidak ada batasan.</div>
                    @error('waktu_mulai_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Waktu Selesai Maksimal</label>
                    <input type="time" name="waktu_selesai_max"
                           class="form-control @error('waktu_selesai_max') is-invalid @enderror"
                           value="{{ old('waktu_selesai_max') }}">
                    <div class="form-text">Meeting harus selesai sebelum atau tepat jam ini. Kosongkan jika tidak ada batasan.</div>
                    @error('waktu_selesai_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
        @endif
    </div>
</div>

@endsection
