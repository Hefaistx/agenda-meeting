@extends('layouts.app')

@section('title', 'Agenda Meeting')
@section('breadcrumb', 'IT > Agenda Meeting')

@section('content')
@php $isManager = true; @endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 style="font-size:15px;font-weight:700;color:#231f20;margin:0">
        <i class="bi bi-calendar3 me-2" style="color:var(--teal)"></i>Agenda Meeting
    </h4>
    @if($isManager)
    <a href="{{ route('agenda.create') }}" class="btn btn-teal btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Agenda
    </a>
    @endif
</div>

{{-- Tabs --}}
@php
    $activeTab = request('tab', 'semua');
    $tabList   = ['semua' => 'Semua'] + array_combine(
        array_map(fn($s) => strtolower(str_replace(' ', '-', $s)), \App\Models\Meeting::$statusOptions),
        \App\Models\Meeting::$statusOptions
    );
    $tabColors = ['To Do' => '#3b5bdb', 'Done' => '#16a34a', 'Cancelled' => '#dc2626', 'Rescheduled' => '#f59e0b'];
@endphp
<div class="d-flex gap-2 flex-wrap mb-3">
    @foreach($tabList as $key => $label)
    @php $cnt = $key === 'semua' ? ($counts['semua'] ?? 0) : ($counts[$label] ?? 0); @endphp
    <a href="{{ route('agenda.index', array_merge(request()->except(['tab','page']), ['tab' => $key])) }}"
       class="btn btn-sm"
       style="border-radius:20px;font-size:12px;font-weight:600;padding:4px 14px;
              {{ $activeTab === $key
                  ? 'background:' . ($key === 'semua' ? '#3b5bdb' : ($tabColors[$label] ?? '#3b5bdb')) . ';color:#fff;border-color:transparent'
                  : 'background:#fff;color:#374151;border:1px solid #d1d5db' }}">
        {{ $label }} ({{ $cnt }})
    </a>
    @endforeach
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-hdr">
        <span>Filter</span>
        <button class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#filterArea">
            <i class="bi bi-plus-lg" id="filterIcon"></i>
        </button>
    </div>
    <div class="collapse {{ request()->hasAny(['tanggal','kategori','status','jam_dari','jam_sampai','pic_internal','pic_external','ruangan_id','topik_id']) ? 'show' : '' }}" id="filterArea">
        <div class="card-body pt-2 pb-3">
            {{-- Data untuk Alpine filter PIC Eksternal --}}
                    @php
                        $_picExtFilterArr  = array_values(array_filter(array_map('trim', explode(',', request('pic_external', '')))));
                        $_initExtDivFilter = [];
                        foreach(\App\Models\Meeting::$externalDivisions as $_c => $_d) {
                            foreach($_picExtFilterArr as $_n) {
                                if(isset($_d['members'][$_n])) { $_initExtDivFilter[] = $_c; break; }
                            }
                        }
                    @endphp
                    <script>
                    var _agendaFiltExtDivs    = @json(\App\Models\Meeting::$externalDivisions);
                    var _agendaFiltInitDivs   = @json($_initExtDivFilter);
                    var _agendaFiltInitPeople = @json($_picExtFilterArr);
                    </script>
        <form method="GET" action="{{ route('agenda.index') }}">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm"
                               value="{{ request('tanggal') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="">Pilih kategori</option>
                            @foreach(\App\Models\Meeting::$kategoriOptions as $k)
                                <option value="{{ $k }}" {{ request('kategori') == $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Pilih status</option>
                            @foreach(\App\Models\Meeting::$statusOptions as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Jam Mulai</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="time" name="jam_dari" class="form-control form-control-sm"
                                   value="{{ request('jam_dari') }}" placeholder="Dari">
                            <span style="color:#9ca3af;font-size:12px;flex-shrink:0">–</span>
                            <input type="time" name="jam_sampai" class="form-control form-control-sm"
                                   value="{{ request('jam_sampai') }}" placeholder="Sampai">
                        </div>
                    </div>

                    {{-- Row 2 --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">PIC Internal</label>
                        @include('partials.pic-dropdown', [
                            'name'    => 'pic_internal',
                            'options' => \App\Models\Meeting::$picOptions,
                            'labels'  => \App\Models\Meeting::$picLabels,
                            'selected'=> array_filter(array_map('trim', explode(',', request('pic_internal', '')))),
                        ])
                    </div>
                    {{-- Divisi Eksternal --}}
                    <div class="col-md-3 col-sm-6"
                         x-data="agendaExtDivPicker(_agendaFiltExtDivs, _agendaFiltInitDivs)">
                        <label class="form-label">Divisi Eksternal</label>
                        <div class="pic-dropdown">
                            <div class="pic-trigger" @click="isDivOpen = !isDivOpen">
                                <template x-if="selected.length === 0">
                                    <span style="color:#9ca3af;font-size:12px">— Pilih Divisi —</span>
                                </template>
                                <template x-for="d in selected" :key="d">
                                    <span class="pic-chip" x-text="d"></span>
                                </template>
                                <i class="bi bi-chevron-down ms-auto" style="font-size:11px;color:#9ca3af;flex-shrink:0"></i>
                            </div>
                            <div class="pic-list" x-show="isDivOpen" x-cloak @click.outside="isDivOpen = false">
                                @foreach(\App\Models\Meeting::$externalDivisions as $code => $div)
                                <label style="display:flex;align-items:flex-start;gap:8px;padding:8px 12px;cursor:pointer;border-bottom:1px solid #f5f5f5;margin:0">
                                    <input type="checkbox" class="form-check-input mt-1"
                                           value="{{ $code }}"
                                           :checked="selected.includes('{{ $code }}')"
                                           @change="toggle('{{ $code }}')">
                                    <div>
                                        <div style="font-size:12.5px;color:#1e2847;font-weight:600">{{ $code }}</div>
                                        <div style="font-size:10.5px;color:#9ca3af">{{ $div['label'] }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- PIC Eksternal (muncul setelah divisi dipilih) --}}
                    <div class="col-md-3 col-sm-6"
                         x-data="agendaExtPeoplePicker(_agendaFiltExtDivs, _agendaFiltInitDivs, _agendaFiltInitPeople)"
                         @agenda-ext-div-changed.window="handleDivChange($event.detail.selected)"
                         x-show="selectedDivisions.length > 0" x-cloak>
                        <label class="form-label">PIC Eksternal</label>
                        <div class="pic-dropdown">
                            <div class="pic-trigger" @click="isPeopleOpen = !isPeopleOpen">
                                <template x-if="selectedPeople.length === 0">
                                    <span style="color:#9ca3af;font-size:12px">— Pilih PIC —</span>
                                </template>
                                <template x-for="p in selectedPeople" :key="p">
                                    <span class="pic-chip" x-text="p"></span>
                                </template>
                                <i class="bi bi-chevron-down ms-auto" style="font-size:11px;color:#9ca3af;flex-shrink:0"></i>
                            </div>
                            <div class="pic-list" x-show="isPeopleOpen" x-cloak @click.outside="isPeopleOpen = false" style="max-height:220px">
                                <template x-for="person in availablePeople" :key="person.name">
                                    <label style="display:flex;align-items:flex-start;gap:8px;padding:7px 12px;cursor:pointer;border-bottom:1px solid #f5f5f5;margin:0">
                                        <input type="checkbox" class="form-check-input mt-1"
                                               :value="person.name"
                                               :checked="selectedPeople.includes(person.name)"
                                               @change="togglePerson(person.name)">
                                        <div>
                                            <div x-text="person.name" style="font-size:12.5px;color:#1e2847;font-weight:500"></div>
                                            <div style="font-size:10.5px;color:#9ca3af">
                                                <span x-text="person.code" style="font-weight:700;color:#4361ee"></span>
                                                <span> · </span><span x-text="person.role"></span>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                        <input type="hidden" name="pic_external" :value="selectedPeople.join(', ')">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Topik</label>
                        <select name="topik_id" class="form-select form-select-sm">
                            <option value="">Semua Topik</option>
                            @foreach($topics as $t)
                                <option value="{{ $t->id }}" {{ request('topik_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Ruangan</label>
                        <select name="ruangan_id" class="form-select form-select-sm">
                            <option value="">Semua Ruangan</option>
                            @foreach(\App\Models\Room::orderBy('nama')->get() as $room)
                                <option value="{{ $room->id }}" {{ request('ruangan_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-sm px-4"
                            style="background:#3b5bdb;border-color:#3b5bdb;color:#fff;font-weight:600">
                        Terapkan
                    </button>
                    <a href="{{ route('agenda.index') }}" class="btn btn-sm px-4"
                       style="background:#e03131;border-color:#e03131;color:#fff;font-weight:600">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width:42px">No</th>
                        <th style="width:120px">ID</th>
                        <th style="width:95px">Tanggal</th>
                        <th style="width:110px">Jam</th>
                        <th style="width:90px">Kategori</th>
                        <th style="min-width:140px">Topik</th>
                        <th>Kegiatan</th>
                        <th style="width:130px">Ruangan</th>
                        <th style="width:105px">Status</th>
                        <th style="width:150px">PIC Internal</th>
                        <th style="width:140px">PIC External</th>
                        <th style="width:80px" class="text-center">Divisi</th>
                        <th style="width:60px" class="text-center">NM</th>
                        <th style="width:{{ $isManager ? '110px' : '50px' }}" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meetings as $idx => $meeting)
                    <tr>
                        <td class="text-center">{{ $meetings->firstItem() + $idx }}</td>
                        <td>
                            <span style="font-family:monospace;font-size:11.5px;color:#6b7280;letter-spacing:0.3px">
                                {{ $meeting->meeting_code ?? '—' }}
                            </span>
                        </td>
                        <td>{{ $meeting->tanggal->format('d-m-Y') }}</td>
                        <td style="font-size:12px;white-space:nowrap">
                            {{ \Carbon\Carbon::parse($meeting->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($meeting->jam_selesai)->format('H:i') }}
                        </td>
                        <td>
                            <span class="badge rounded-pill"
                                  style="background:{{ $meeting->kategori_color }};font-size:10.5px;font-weight:600;padding:4px 9px">
                                {{ $meeting->kategori }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:#374151">{{ $meeting->topic?->nama ?? '–' }}</td>
                        <td style="max-width:200px">{{ $meeting->kegiatan }}</td>
                        <td style="font-size:12.5px">
                            @if($meeting->ruangan)
                                {{ $meeting->ruangan->nama }}
                                @if($meeting->ruangan->lokasi)
                                <div style="font-size:11px;color:#9ca3af">{{ $meeting->ruangan->lokasi }}</div>
                                @endif
                            @else
                                <span style="color:#374151">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-status bg-{{ $meeting->status_badge }}">
                                {{ $meeting->status ?? 'To Do' }}
                            </span>
                        </td>
                        <td>
                            @if($meeting->pic_internal)
                            @php $pics = array_values(array_filter(array_map('trim', explode(',', $meeting->pic_internal)))); @endphp
                            @foreach($pics as $i => $pt)
                                @if($i >= 5) @break @endif
                                <span class="pic-chip">{{ $pt }}</span>
                            @endforeach
                            @if(count($pics) > 5)
                                <span class="pic-chip" style="background:#e2e8f0;color:#6b7280;font-weight:700">+{{ count($pics) - 5 }}</span>
                            @endif
                            @else
                            <span style="color:#9ca3af">-</span>
                            @endif
                        </td>
                        <td>
                            @if($meeting->pic_external)
                            @php $picsExt = array_values(array_filter(array_map('trim', explode(',', $meeting->pic_external)))); @endphp
                            @foreach($picsExt as $i => $pe)
                                @if($i >= 5) @break @endif
                                <span class="pic-chip" title="{{ \App\Models\Meeting::externalPicLabel($pe) }}">
                                    {{ \App\Models\Meeting::externalPicLabel($pe) }}
                                </span>
                            @endforeach
                            @if(count($picsExt) > 5)
                                <span class="pic-chip" style="background:#e2e8f0;color:#6b7280;font-weight:700">+{{ count($picsExt) - 5 }}</span>
                            @endif
                            @else
                            <span style="color:#9ca3af">-</span>
                            @endif
                        </td>
                        {{-- Divisi --}}
                        <td class="text-center">
                            @if($meeting->pic_external)
                            @foreach(\App\Models\Meeting::externalPicDivisions($meeting->pic_external) as $div)
                            <span class="badge" style="background:#e8f4ff;color:#1e2847;font-size:10px;font-weight:700;margin:1px">{{ $div }}</span>
                            @endforeach
                            @else
                            <span style="color:#9ca3af">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($meeting->link_nm)
                                <a href="{{ $meeting->link_nm }}" target="_blank"
                                   class="text-success" data-bs-toggle="tooltip" title="Lihat NM">
                                    <i class="bi bi-file-earmark-check-fill"></i>
                                </a>
                            @elseif($meeting->nm_file)
                                <a href="{{ asset($meeting->nm_file) }}" target="_blank"
                                   class="text-success" data-bs-toggle="tooltip" title="Lihat NM (file)">
                                    <i class="bi bi-file-earmark-check-fill"></i>
                                </a>
                            @else
                                <span style="color:#374151">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1" style="flex-wrap:nowrap">
                                {{-- View (semua role, semua status) --}}
                                <a href="{{ route('agenda.show', $meeting) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   style="font-size:11px;padding:3px 7px"
                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if($isManager && $meeting->status !== 'Cancelled')
                                {{-- Upload NM --}}
                                @if(!$meeting->link_nm && !$meeting->nm_file && $meeting->status !== 'Done')
                                <button class="btn btn-sm btn-outline-success btn-upload-nm"
                                        style="font-size:11px;padding:3px 7px"
                                        data-id="{{ $meeting->id }}"
                                        data-bs-toggle="tooltip" title="Upload NM">
                                    <i class="bi bi-upload"></i>
                                </button>
                                @endif

                                {{-- Cancel --}}
                                @if($meeting->status !== 'Done')
                                <form method="POST" action="{{ route('agenda.cancel', $meeting) }}"
                                      onsubmit="return confirm('Batalkan meeting ini?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                            style="font-size:11px;padding:3px 7px"
                                            data-bs-toggle="tooltip" title="Cancel">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Delete --}}
                                <form method="POST" action="{{ route('agenda.destroy', $meeting) }}"
                                      onsubmit="return confirm('Hapus agenda ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            style="font-size:11px;padding:3px 7px"
                                            data-bs-toggle="tooltip" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-5" style="color:#9ca3af">
                            <i class="bi bi-calendar-x" style="font-size:28px;display:block;margin-bottom:8px"></i>
                            Belum ada agenda meeting
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($meetings->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-2"
             style="border-top:1px solid #eef0f3">
            <div style="font-size:12px;color:#6b7280">
                Menampilkan {{ $meetings->firstItem() }}–{{ $meetings->lastItem() }}
                dari {{ $meetings->total() }} data
            </div>
            {{ $meetings->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Input Link NM Modal --}}
<div class="modal fade" id="uploadNmModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="bi bi-link-45deg me-2" style="color:var(--teal)"></i>Input Link Notula Meeting</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadNmForm" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <label class="form-label">Link NM</label>
                    <input type="url" name="link_nm" id="linkNmInput"
                           class="form-control form-control-sm"
                           placeholder="https://drive.google.com/..." required>
                    <div class="alert alert-info mt-3 mb-0 py-2" style="font-size:11.5px">
                        <i class="bi bi-info-circle me-1"></i>
                        Status akan otomatis berubah ke <strong>Done</strong> setelah disimpan.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-teal">
                        <i class="bi bi-check-lg me-1"></i>Simpan & Selesaikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reschedule modal moved to agenda/show.blade.php --}}
<div id="rescheduleWrapper" style="display:none"
     x-data="{
         id: null,
         tanggalLama: '',
         jamLama: '',
         tanggalBaru: '',
         jamMulai: '',
         jamSelesai: '',
         selectedPic: [],
         picExternal: '',
         ruanganId: '',
         picOptions: {{ json_encode(\App\Models\Meeting::$picOptions) }},
         rooms: {{ json_encode($rooms->map(fn($r) => ['id' => $r->id, 'label' => $r->nama . ($r->lokasi ? ' · ' . $r->lokasi : '')])->values()) }},
         get rescheduleUrl() { return this.id ? `/agenda/${this.id}/reschedule` : '#' },
         open(data) {
             this.id          = data.id
             this.tanggalLama = data.tanggal
             this.jamLama     = data.jamMulai + '–' + data.jamSelesai
             this.tanggalBaru = data.tanggal
             this.jamMulai    = data.jamMulai
             this.jamSelesai  = data.jamSelesai
             this.selectedPic = data.picInternal ? data.picInternal.split(',').map(s => s.trim()).filter(Boolean) : []
             this.picExternal = data.picExternal || ''
             this.ruanganId   = data.ruanganId ? String(data.ruanganId) : ''
             bootstrap.Modal.getOrCreateInstance(document.getElementById('rescheduleModal')).show()
         },
         togglePic(v) {
             if (this.selectedPic.includes(v)) this.selectedPic = this.selectedPic.filter(x => x !== v)
             else this.selectedPic.push(v)
         }
     }"
     @reschedule-open.window="open($event.detail)">

    <div class="modal fade" id="rescheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">
                        <i class="bi bi-arrow-repeat me-2" style="color:#f59e0b"></i>Reschedule Meeting
                    </h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <form :action="rescheduleUrl" method="POST">
                    @csrf
                    <div class="modal-body py-3">
                        <div class="alert alert-warning py-2 mb-3" style="font-size:11.5px">
                            <i class="bi bi-info-circle me-1"></i>
                            Jadwal lama: <strong x-text="tanggalLama + ' · ' + jamLama"></strong>
                        </div>

                        {{-- Tanggal baru --}}
                        <div class="mb-2">
                            <label class="form-label">Tanggal Baru <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_baru" x-model="tanggalBaru"
                                   class="form-control form-control-sm" required>
                        </div>

                        {{-- Jam range --}}
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai_baru" x-model="jamMulai"
                                       class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai_baru" x-model="jamSelesai"
                                       class="form-control form-control-sm" required>
                            </div>
                        </div>

                        {{-- Ruangan --}}
                        <div class="mb-2">
                            <label class="form-label">Ruangan</label>
                            <select name="ruangan_id" class="form-select form-select-sm" x-model="ruanganId">
                                <option value="">— Pilih Ruangan —</option>
                                <template x-for="r in rooms" :key="r.id">
                                    <option :value="String(r.id)" :selected="ruanganId === String(r.id)" x-text="r.label"></option>
                                </template>
                            </select>
                        </div>

                        {{-- PIC Internal --}}
                        <div class="mb-2" x-data="{ picOpen: false }">
                            <label class="form-label">PIC Internal</label>
                            <div class="pic-dropdown">
                                <div class="pic-trigger" @click="picOpen = !picOpen">
                                    <template x-if="selectedPic.length === 0">
                                        <span style="color:#9ca3af;font-size:12px">— Pilih PIC —</span>
                                    </template>
                                    <template x-for="p in selectedPic" :key="p">
                                        <span class="pic-chip" x-text="p"></span>
                                    </template>
                                    <i class="bi bi-chevron-down ms-auto" style="font-size:11px;color:#9ca3af;flex-shrink:0"></i>
                                </div>
                                <div class="pic-list" x-show="picOpen" x-cloak @click.outside="picOpen = false">
                                    <template x-for="opt in picOptions" :key="opt">
                                        <label>
                                            <input type="checkbox" class="form-check-input"
                                                   :value="opt"
                                                   :checked="selectedPic.includes(opt)"
                                                   @change="togglePic(opt)">
                                            <span x-text="opt"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <input type="hidden" name="pic_internal" :value="selectedPic.join(', ')">
                        </div>

                        {{-- PIC External --}}
                        <div class="mb-2">
                            <label class="form-label">PIC Eksternal</label>
                            <input type="text" name="pic_external" x-model="picExternal"
                                   class="form-control form-control-sm"
                                   placeholder="Contoh: Bu Shinta, Bima">
                        </div>

                        {{-- Alasan --}}
                        <div class="mb-0">
                            <label class="form-label">Alasan <span class="text-muted" style="font-size:11px">(opsional)</span></label>
                            <input type="text" name="alasan"
                                   class="form-control form-control-sm"
                                   placeholder="Mis: konflik jadwal, ruangan tidak tersedia...">
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm"
                                style="background:#f59e0b;border-color:#f59e0b;color:#fff;font-weight:600">
                            <i class="bi bi-check-lg me-1"></i>Simpan Reschedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function agendaExtDivPicker(extDivisions, initSelected) {
    return {
        extDivisions,
        selected: initSelected || [],
        isDivOpen: false,
        toggle(code) {
            if (this.selected.includes(code)) {
                this.selected = this.selected.filter(x => x !== code)
            } else {
                this.selected.push(code)
            }
            window.dispatchEvent(new CustomEvent('agenda-ext-div-changed', { detail: { selected: this.selected, extDivisions: this.extDivisions } }))
        }
    }
}

function agendaExtPeoplePicker(extDivisions, initDivisions, initPeople) {
    return {
        extDivisions,
        selectedDivisions: initDivisions || [],
        selectedPeople:    initPeople    || [],
        isPeopleOpen: false,
        get availablePeople() {
            const list = []
            for (const [code, div] of Object.entries(this.extDivisions)) {
                if (this.selectedDivisions.includes(code)) {
                    for (const [name, role] of Object.entries(div.members || {})) {
                        list.push({ name, role, code })
                    }
                }
            }
            return list
        },
        handleDivChange(selected) {
            const removed = this.selectedDivisions.filter(d => !selected.includes(d))
            for (const d of removed) {
                const members = Object.keys(this.extDivisions[d]?.members || {})
                this.selectedPeople = this.selectedPeople.filter(p => !members.includes(p))
            }
            this.selectedDivisions = selected
        },
        togglePerson(name) {
            if (this.selectedPeople.includes(name)) {
                this.selectedPeople = this.selectedPeople.filter(x => x !== name)
            } else {
                this.selectedPeople.push(name)
            }
        }
    }
}

const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]')
tooltips.forEach(el => new bootstrap.Tooltip(el))

// Toggle icon filter accordion
const filterArea = document.getElementById('filterArea')
const filterIcon = document.getElementById('filterIcon')
if (filterArea && filterIcon) {
    filterArea.addEventListener('show.bs.collapse', () => filterIcon.className = 'bi bi-dash-lg')
    filterArea.addEventListener('hide.bs.collapse', () => filterIcon.className = 'bi bi-plus-lg')
    if (filterArea.classList.contains('show')) filterIcon.className = 'bi bi-dash-lg'
}

const uploadModal = new bootstrap.Modal(document.getElementById('uploadNmModal'))

document.querySelectorAll('.btn-upload-nm').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id
        document.getElementById('uploadNmForm').action = `/agenda/${id}/upload-nm`
        document.getElementById('linkNmInput').value = ''
        uploadModal.show()
    })
})

</script>
@endpush
