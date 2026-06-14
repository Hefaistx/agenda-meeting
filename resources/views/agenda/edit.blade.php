@extends('layouts.app')

@php $isReschedule = request('mode') === 'reschedule'; @endphp

@section('title', $isReschedule ? 'Reschedule Meeting' : 'Edit Agenda Meeting')
@section('breadcrumb', 'IT > Agenda Meeting > ' . ($isReschedule ? 'Reschedule' : 'Edit'))

@section('content')

<div class="page-hdr">
    <h4>
        <i class="bi bi-{{ $isReschedule ? 'arrow-repeat' : 'calendar-check' }} me-2"
           style="color:{{ $isReschedule ? '#f59e0b' : 'var(--teal)' }}"></i>
        {{ $isReschedule ? 'Reschedule Meeting' : 'Edit Agenda Meeting' }}
    </h4>
    <a href="{{ route('agenda.show', $agenda) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@if($isReschedule)
<div class="alert alert-warning py-2 mb-3" style="font-size:12px">
    <i class="bi bi-info-circle me-1"></i>
    Jadwal lama: <strong>{{ $agenda->tanggal->format('d M Y') }} · {{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i') }}</strong>
</div>
@endif

<div class="card">
    <div class="card-body pt-3">
            <form method="POST" action="{{ $isReschedule ? route('agenda.reschedule', $agenda) : route('agenda.update', $agenda) }}">
                @csrf
                @if(!$isReschedule) @method('PUT') @endif
                <div class="row g-3">

                    {{-- Tanggal --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" required
                               class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal') ?: $agenda->tanggal->format('Y-m-d') }}">
                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Jam Mulai --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                        @include('partials.time-picker', [
                            'name'  => 'jam_mulai',
                            'value' => old('jam_mulai', \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i')),
                        ])
                        @error('jam_mulai')<div class="text-danger mt-1" style="font-size:11.5px">{{ $message }}</div>@enderror
                    </div>

                    {{-- Jam Selesai --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                        @include('partials.time-picker', [
                            'name'  => 'jam_selesai',
                            'value' => old('jam_selesai', \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i')),
                        ])
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
                                <option value="{{ $k }}" {{ old('kategori', $agenda->kategori) == $k ? 'selected' : '' }}>{{ $k }}</option>
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
                                <option value="{{ $topik->id }}"
                                    {{ old('topik_id', $agenda->topik_id) == $topik->id ? 'selected' : '' }}>
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
                                required>
                            <option value="">— Pilih Ruangan —</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}"
                                    {{ old('ruangan_id', $agenda->ruangan_id) == $room->id ? 'selected' : '' }}>
                                    {{ $room->nama }}{{ $room->lokasi ? ' · ' . $room->lokasi : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('ruangan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(\App\Models\Meeting::$statusOptions as $s)
                                <option value="{{ $s }}" {{ old('status', $agenda->status) == $s ? 'selected' : '' }}>
                                    {{ $s }}
                                </option>
                            @endforeach
                        </select>
                        @if($agenda->nm_file)
                        <small class="text-success mt-1 d-block">
                            <i class="bi bi-file-earmark-check me-1"></i>NM sudah diupload
                        </small>
                        @endif
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Kegiatan --}}
                    <div class="col-12">
                        <label class="form-label">Kegiatan / Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="kegiatan" rows="3"
                                  class="form-control @error('kegiatan') is-invalid @enderror">{{ old('kegiatan', $agenda->kegiatan) }}</textarea>
                        @error('kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- PIC Internal --}}
                    <div class="col-md-6">
                        <label class="form-label">PIC Internal</label>
                        @php $selectedPics = array_filter(array_map('trim', explode(',', old('pic_internal', $agenda->pic_internal ?? '')))); @endphp
                        @include('partials.pic-dropdown', [
                            'name'     => 'pic_internal',
                            'options'  => \App\Models\Meeting::$picOptions,
                            'labels'   => \App\Models\Meeting::$picLabels,
                            'selected' => $selectedPics,
                        ])
                        @error('pic_internal')<div class="text-danger mt-1" style="font-size:11.5px">{{ $message }}</div>@enderror
                    </div>

                    {{-- PIC Eksternal (muncul saat Kategori = External) --}}
                    @php
                        $_picExtArr   = array_values(array_filter(array_map('trim', explode(',', old('pic_external', $agenda->pic_external ?? '')))));
                        $_initExtDivs = [];
                        foreach(\App\Models\Meeting::$externalDivisions as $_c => $_d) {
                            foreach($_picExtArr as $_n) {
                                if(isset($_d['members'][$_n])) { $_initExtDivs[] = $_c; break; }
                            }
                        }
                    @endphp
                    <script>
                    var _editExtDivisions   = @json(\App\Models\Meeting::$externalDivisions);
                    var _editInitExtDivs    = @json($_initExtDivs);
                    var _editInitExtPeople  = @json($_picExtArr);
                    </script>
                    <div class="col-md-6"
                         x-data="{ showExt: '{{ old('kategori', $agenda->kategori) }}' === 'External' }"
                         @kategori-changed.window="showExt = $event.detail.value === 'External'"
                         x-show="showExt" x-cloak>
                        <label class="form-label">PIC Eksternal</label>
                        <div x-data="picExtFormField(_editExtDivisions, _editInitExtDivs, _editInitExtPeople)">

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

                    {{-- Hasil --}}
                    <div class="col-12">
                        <label class="form-label">Hasil / Catatan</label>
                        <textarea name="hasil" rows="3"
                                  class="form-control @error('hasil') is-invalid @enderror"
                                  placeholder="Hasil atau catatan dari meeting...">{{ old('hasil', $agenda->hasil) }}</textarea>
                        @error('hasil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Reschedule History --}}
                    @if(!empty($agenda->reschedule_history))
                    <div class="col-12">
                        <label class="form-label mb-2" style="font-weight:700;color:#374151">
                            <i class="bi bi-clock-history me-1" style="color:#f59e0b"></i>
                            Riwayat Reschedule ({{ count($agenda->reschedule_history) }}x)
                        </label>
                        <div style="border:1px solid #fde68a;border-radius:8px;overflow:hidden">
                            @foreach(array_reverse($agenda->reschedule_history) as $i => $h)
                            @php
                                $dariJam = isset($h['dari_jam_mulai'])
                                    ? $h['dari_jam_mulai'] . '–' . $h['dari_jam_selesai']
                                    : ($h['dari_jam'] ?? '?');
                                $keJam = isset($h['ke_jam_mulai'])
                                    ? $h['ke_jam_mulai'] . '–' . $h['ke_jam_selesai']
                                    : ($h['ke_jam'] ?? '?');
                                $byUser = $h['rescheduled_by'] ?? $h['oleh'] ?? '—';
                                $atTime = $h['rescheduled_at'] ?? $h['pada'] ?? '—';
                            @endphp
                            <div style="padding:10px 14px;{{ $i > 0 ? 'border-top:1px solid #fde68a;' : '' }}background:{{ $i === 0 ? '#fffbeb' : '#fff' }}">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <span style="font-size:11px;font-weight:700;color:#92400e">
                                            #{{ count($agenda->reschedule_history) - $i }}
                                        </span>
                                        <span style="font-size:12px;color:#374151;margin-left:8px">
                                            <span style="text-decoration:line-through;color:#9ca3af">
                                                {{ \Carbon\Carbon::parse($h['dari_tanggal'])->format('d-m-Y') }} {{ $dariJam }}
                                            </span>
                                            <i class="bi bi-arrow-right mx-1" style="color:#f59e0b;font-size:11px"></i>
                                            <strong>{{ \Carbon\Carbon::parse($h['ke_tanggal'])->format('d-m-Y') }} {{ $keJam }}</strong>
                                        </span>
                                        @if(!empty($h['alasan']))
                                        <div style="font-size:11.5px;color:#6b7280;margin-top:3px">
                                            <i class="bi bi-chat-text me-1"></i>{{ $h['alasan'] }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="text-end" style="flex-shrink:0">
                                        <div style="font-size:11px;color:#9ca3af">{{ $byUser }}</div>
                                        <div style="font-size:10.5px;color:#d1d5db">{{ $atTime }}</div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="col-12">
                        <small class="text-muted">
                            Dibuat: {{ $agenda->created_at->format('d-m-Y H:i') }}
                            &nbsp;·&nbsp;
                            Diperbarui: {{ $agenda->updated_at->format('d-m-Y H:i') }}
                        </small>
                    </div>

                    @if($isReschedule)
                    <div class="col-12">
                        <label class="form-label">Alasan Reschedule <span class="text-muted" style="font-size:11px">(opsional)</span></label>
                        <input type="text" name="alasan" class="form-control @error('alasan') is-invalid @enderror"
                               placeholder="Mis: konflik jadwal, ruangan tidak tersedia..."
                               value="{{ old('alasan') }}">
                        @error('alasan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @endif

                    <div class="col-12 d-flex gap-2 pt-2"
                         style="border-top:1px solid #e2e8f0;margin-top:4px">
                        @if($isReschedule)
                        <button type="submit" class="btn btn-sm px-4"
                                style="background:#f59e0b;border-color:#f59e0b;color:#fff;font-weight:600">
                            <i class="bi bi-arrow-repeat me-1"></i>Simpan Reschedule
                        </button>
                        @else
                        <button type="submit" class="btn btn-sm px-4"
                                style="background:#3b5bdb;border-color:#3b5bdb;color:#fff;font-weight:600">
                            Submit
                        </button>
                        @endif
                        <a href="{{ route('agenda.show', $agenda) }}" class="btn btn-sm px-4"
                           style="background:#e03131;border-color:#e03131;color:#fff;font-weight:600">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
    </div>
</div>

@endsection
