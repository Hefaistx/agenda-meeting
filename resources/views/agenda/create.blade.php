@extends('layouts.app')

@section('title', 'Tambah Agenda Meeting')
@section('breadcrumb', 'IT > Agenda Meeting > Tambah')

@section('content')

<div class="mb-3">
    <a href="{{ route('agenda.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body pt-3">
        <div x-data="conflictChecker({
                tanggal:     '{{ old('tanggal', date('Y-m-d')) }}',
                jamMulai:    '{{ old('jam_mulai', '08:00') }}',
                jamSelesai:  '{{ old('jam_selesai', '09:00') }}',
                ruanganId:   '{{ old('ruangan_id', '') }}',
                picInternal: '{{ old('pic_internal', '') }}',
                excludeId:   null
             })"
             @time-changed.window="onTimeChanged($event.detail)"
             @pic-internal-changed.window="onPicChanged($event.detail)">

            {{-- Conflict Warning --}}
            <template x-if="hasConflicts">
                <div class="alert mb-3 py-2" style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px">
                    <div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:4px">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Peringatan Konflik Jadwal
                    </div>
                    <template x-if="conflicts.room_conflict">
                        <div style="font-size:12px;color:#78350f;margin-bottom:2px">
                            <i class="bi bi-door-open me-1"></i>
                            Ruangan sudah digunakan:
                            <strong x-text="conflicts.room_conflict.meeting_code"></strong>
                            (<span x-text="conflicts.room_conflict.jam"></span>)
                            — <span x-text="conflicts.room_conflict.kegiatan" style="font-style:italic"></span>
                        </div>
                    </template>
                    <template x-for="p in (conflicts.pic_conflicts || [])" :key="p.name">
                        <div style="font-size:12px;color:#78350f">
                            <i class="bi bi-person-fill-exclamation me-1"></i>
                            <strong x-text="p.name"></strong> sudah ada jadwal:
                            <span x-text="p.meeting_code" style="font-family:monospace;font-size:11px"></span>
                            (<span x-text="p.jam"></span>)
                        </div>
                    </template>
                    <div style="font-size:11px;color:#a16207;margin-top:4px">
                        <i class="bi bi-info-circle me-1"></i>Agenda tetap bisa disimpan meski ada konflik.
                    </div>
                </div>
            </template>

            <form method="POST" action="{{ route('agenda.store') }}" @submit.prevent="handleSubmit($event)">
                @csrf
                <div class="row g-3">

                    {{-- Tanggal --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal"
                               class="form-control @error('tanggal') is-invalid @enderror"
                               x-model="tanggal"
                               @change="scheduleCheck()"
                               value="{{ old('tanggal', date('Y-m-d')) }}">
                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Jam Mulai --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                        @include('partials.time-picker', ['name' => 'jam_mulai', 'value' => old('jam_mulai', '08:00')])
                        @error('jam_mulai')<div class="text-danger mt-1" style="font-size:11.5px">{{ $message }}</div>@enderror
                    </div>

                    {{-- Jam Selesai --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                        @include('partials.time-picker', ['name' => 'jam_selesai', 'value' => old('jam_selesai', '09:00')])
                        @error('jam_selesai')<div class="text-danger mt-1" style="font-size:11.5px">{{ $message }}</div>@enderror
                    </div>

                    {{-- Kategori --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori"
                                class="form-select @error('kategori') is-invalid @enderror"
                                @change="$dispatch('kategori-changed', { value: $event.target.value })">
                            <option value="">— Pilih Kategori —</option>
                            @foreach(\App\Models\Meeting::$kategoriOptions as $k)
                                <option value="{{ $k }}" {{ old('kategori') == $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                        @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Topik --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Topik</label>
                        <select name="topik_id" class="form-select @error('topik_id') is-invalid @enderror">
                            <option value="">— Pilih Topik —</option>
                            @foreach($topics as $topik)
                                <option value="{{ $topik->id }}" {{ old('topik_id') == $topik->id ? 'selected' : '' }}>
                                    {{ $topik->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('topik_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Ruangan --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                        <select name="ruangan_id"
                                class="form-select @error('ruangan_id') is-invalid @enderror"
                                x-model="ruanganId"
                                @change="scheduleCheck()"
                                required>
                            <option value="">— Pilih Ruangan —</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('ruangan_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->nama }}{{ $room->lokasi ? ' · ' . $room->lokasi : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('ruangan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Kegiatan --}}
                    <div class="col-12">
                        <label class="form-label">Kegiatan / Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="kegiatan" rows="3"
                                  class="form-control @error('kegiatan') is-invalid @enderror"
                                  placeholder="Deskripsi kegiatan meeting...">{{ old('kegiatan') }}</textarea>
                        @error('kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- PIC Internal --}}
                    <div class="col-md-6">
                        <label class="form-label">PIC Internal</label>
                        <div :class="picError ? 'pic-error-wrap' : ''">
                            @include('partials.pic-dropdown', [
                                'name'          => 'pic_internal',
                                'dispatchEvent' => 'pic-internal-changed',
                                'options'       => \App\Models\Meeting::$picOptions,
                                'labels'        => \App\Models\Meeting::$picLabels,
                                'selected'      => array_filter(array_map('trim', explode(',', old('pic_internal', '')))),
                            ])
                        </div>
                        <div x-show="picError" x-cloak style="font-size:11.5px;color:#dc2626;margin-top:4px">
                            <i class="bi bi-exclamation-circle me-1"></i><span x-text="picErrorMsg"></span>
                        </div>
                        @error('pic_internal')<div class="text-danger mt-1" style="font-size:11.5px">{{ $message }}</div>@enderror
                    </div>

                    {{-- PIC Eksternal (muncul saat Kategori = External) --}}
                    @php
                        $_picExtArr   = array_values(array_filter(array_map('trim', explode(',', old('pic_external', '')))));
                        $_initExtDivs = [];
                        foreach(\App\Models\Meeting::$externalDivisions as $_c => $_d) {
                            foreach($_picExtArr as $_n) {
                                if(isset($_d['members'][$_n])) { $_initExtDivs[] = $_c; break; }
                            }
                        }
                    @endphp
                    <script>
                    var _createExtDivisions = @json(\App\Models\Meeting::$externalDivisions);
                    var _createInitExtDivs  = @json($_initExtDivs);
                    var _createInitExtPeople = @json($_picExtArr);
                    </script>
                    <div class="col-md-6"
                         x-data="{ showExt: '{{ old('kategori') }}' === 'External' }"
                         @kategori-changed.window="showExt = $event.detail.value === 'External'"
                         x-show="showExt" x-cloak>
                        <label class="form-label">PIC Eksternal</label>
                        <div x-data="picExtFormField(_createExtDivisions, _createInitExtDivs, _createInitExtPeople)">

                            {{-- Step 1: Divisi --}}
                            <div class="mb-2">
                                <div style="font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px">Divisi</div>
                                <div class="pic-dropdown">
                                    <div class="pic-trigger" @click="openDivDrop = !openDivDrop">
                                        <template x-if="selectedDivisions.length === 0">
                                            <span style="color:#9ca3af;font-size:12px">— Pilih Divisi —</span>
                                        </template>
                                        <template x-for="d in selectedDivisions" :key="d">
                                            <span class="pic-chip" x-text="d"></span>
                                        </template>
                                        <i class="bi bi-chevron-down ms-auto" style="font-size:11px;color:#9ca3af;flex-shrink:0"></i>
                                    </div>
                                    <div class="pic-list" x-show="openDivDrop" @click.outside="openDivDrop = false">
                                        @foreach(\App\Models\Meeting::$externalDivisions as $code => $div)
                                        <label style="display:flex;align-items:flex-start;gap:8px;padding:8px 12px;cursor:pointer;border-bottom:1px solid #f5f5f5;margin:0">
                                            <input type="checkbox" class="form-check-input mt-1"
                                                   value="{{ $code }}"
                                                   :checked="selectedDivisions.includes('{{ $code }}')"
                                                   @change="toggleDiv('{{ $code }}')">
                                            <div>
                                                <div style="font-size:12.5px;color:#1e2847;font-weight:600">{{ $code }}</div>
                                                <div style="font-size:10.5px;color:#9ca3af">{{ $div['label'] }}</div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Step 2: Orang (muncul setelah divisi dipilih) --}}
                            <div x-show="selectedDivisions.length > 0">
                                <div style="font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px">Orang</div>
                                <div class="pic-dropdown">
                                    <div class="pic-trigger" @click="openPeopleDrop = !openPeopleDrop">
                                        <template x-if="selectedPeople.length === 0">
                                            <span style="color:#9ca3af;font-size:12px">— Pilih PIC —</span>
                                        </template>
                                        <template x-for="p in selectedPeople" :key="p">
                                            <span class="pic-chip" x-text="p"></span>
                                        </template>
                                        <i class="bi bi-chevron-down ms-auto" style="font-size:11px;color:#9ca3af;flex-shrink:0"></i>
                                    </div>
                                    <div class="pic-list" x-show="openPeopleDrop" @click.outside="openPeopleDrop = false" style="max-height:220px">
                                        <template x-for="person in availablePeople" :key="person.name">
                                            <label style="display:flex;align-items:flex-start;gap:8px;padding:7px 12px;cursor:pointer;border-bottom:1px solid #f5f5f5;margin:0">
                                                <input type="checkbox" class="form-check-input mt-1"
                                                       :value="person.name"
                                                       :checked="selectedPeople.includes(person.name)"
                                                       @change="togglePerson(person.name)">
                                                <div>
                                                    <div x-text="person.name" style="font-size:12.5px;color:#1e2847;font-weight:500"></div>
                                                    <div style="font-size:10.5px;color:#9ca3af">
                                                        <span x-text="person.div" style="font-weight:700;color:#4361ee"></span>
                                                        <span> · </span><span x-text="person.role"></span>
                                                    </div>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="pic_external" :value="selectedPeople.join(', ')">
                        </div>
                        @error('pic_external')<div class="text-danger mt-1" style="font-size:11.5px">{{ $message }}</div>@enderror
                    </div>

                    {{-- Catatan --}}
                    <div class="col-12">
                        <label class="form-label">Catatan Awal</label>
                        <textarea name="hasil" rows="2"
                                  class="form-control @error('hasil') is-invalid @enderror"
                                  placeholder="Catatan atau agenda detail...">{{ old('hasil') }}</textarea>
                        @error('hasil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Actions --}}
                    <div class="col-12 d-flex gap-2 pt-2"
                         style="border-top:1px solid #e2e8f0;margin-top:4px">
                        <button type="submit" class="btn btn-sm px-4"
                                style="background:#3b5bdb;border-color:#3b5bdb;color:#fff;font-weight:600">
                            Submit
                        </button>
                        <a href="{{ route('agenda.index') }}" class="btn btn-sm px-4"
                           style="background:#e03131;border-color:#e03131;color:#fff;font-weight:600">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
