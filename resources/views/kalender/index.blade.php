@extends('layouts.app')

@section('title', 'Kalender Meeting')
@section('breadcrumb', 'IT > Kalender Meeting')

@php
$picRoleMap = [];
foreach (\App\Models\Meeting::$picOptions as $pic) {
    $picRoleMap[strtolower($pic)] = [
        'role'     => \App\Models\Meeting::$picRoles[$pic] ?? 'Staff IT',
        'fullName' => $pic,
    ];
}
@endphp

@push('styles')
<style>
    /* ── Calendar cell: summary ── */
    .cal-cell { min-height: 88px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 6px 7px; }
    .cal-cell.has-meetings { cursor: pointer; }
    .cal-cell.has-meetings:hover { background: #f0fbff; }
    .cal-cell.selected-date { background: #e8f6fc !important; outline: 2px solid var(--teal); outline-offset: -2px; }
    .cal-count { font-size: 11px; font-weight: 700; color: #1e2847; margin-bottom: 3px; }
    .cal-status-summary { font-size: 10px; line-height: 1.65; }
    .cal-status-line { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* ── Filter PIC multi-select ── */
    .filter-pic-wrap { position: relative; }
    .filter-pic-trigger {
        min-height: 31px; border: 1px solid var(--border); border-radius: 5px;
        padding: 3px 8px; cursor: pointer; background: #fff;
        display: flex; align-items: center; flex-wrap: wrap; gap: 3px;
        font-size: 12.5px;
    }
    .filter-pic-trigger:hover { border-color: #adb5bd; }
    .filter-pic-list {
        position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 1050;
        background: #fff; border: 1px solid var(--border); border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-height: 200px; overflow-y: auto;
    }
    .filter-pic-list label {
        display: flex; align-items: center; gap: 8px; padding: 7px 12px;
        cursor: pointer; font-size: 12.5px; border-bottom: 1px solid #f5f5f5;
        margin: 0; user-select: none;
    }
    .filter-pic-list label:last-child { border-bottom: none; }
    .filter-pic-list label:hover { background: #f8fafc; }

    /* ── Table ── */
    #meetingTable tbody tr { transition: background 0.12s; cursor: pointer; }
    #meetingTable tbody tr:hover { background: #f0fbff; }
    .row-highlight { animation: rowFlash 1.8s ease; }
    @keyframes rowFlash { 0%,60% { background: #fef3c7; } 100% { background: transparent; } }
    .no-results-row td { padding: 28px 12px; color: #9ca3af; font-style: italic; }
    .btn-aksi { padding: 2px 9px; font-size: 11px; }
</style>
@endpush

@section('content')

@php
    $monthNames = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $dayNames    = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
    $rawDow      = $currentDate->dayOfWeek;
    $startOffset = ($rawDow === 0) ? 6 : $rawDow - 1;
    $daysInMonth = $currentDate->daysInMonth;
    $todayKey    = now('Asia/Jakarta')->format('Y-m-d');
    $statusColors = [
        'To Do'       => '#3b5bdb',
        'Done'        => '#16a34a',
        'Cancelled'   => '#dc2626',
        'Rescheduled' => '#f59e0b',
    ];
@endphp

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-hdr">
        <span>Filter</span>
        <button class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#kalFilterArea">
            <i class="bi bi-plus-lg" id="kalFilterIcon"></i>
        </button>
    </div>
    <div class="collapse {{ request()->hasAny(['tanggal','kategori','status','pic_internal','pic_external','ruangan_id','topik_id']) ? 'show' : '' }}" id="kalFilterArea">
        <div class="card-body pt-2 pb-3">
            @php
                $_picExtKalArr   = array_values(array_filter(array_map('trim', explode(',', request('pic_external', '')))));
                $_initExtKalDivs = [];
                foreach(\App\Models\Meeting::$externalDivisions as $_c => $_d) {
                    foreach($_picExtKalArr as $_n) {
                        if(isset($_d['members'][$_n])) { $_initExtKalDivs[] = $_c; break; }
                    }
                }
            @endphp
            <script>
            var _kalFiltExtDivs    = @json(\App\Models\Meeting::$externalDivisions);
            var _kalFiltInitDivs   = @json($_initExtKalDivs);
            var _kalFiltInitPeople = @json($_picExtKalArr);

            function kalExtDivPicker(extDivisions, initSelected) {
                return {
                    extDivisions: extDivisions,
                    selected: initSelected || [],
                    isDivOpen: false,
                    toggle: function(code) {
                        if (this.selected.includes(code)) {
                            this.selected = this.selected.filter(function(x) { return x !== code });
                        } else {
                            this.selected.push(code);
                        }
                        window.dispatchEvent(new CustomEvent('kal-ext-div-changed', {
                            detail: { selected: this.selected, extDivisions: this.extDivisions }
                        }));
                    }
                };
            }

            function kalExtPeoplePicker(extDivisions, initDivisions, initPeople) {
                return {
                    extDivisions: extDivisions,
                    selectedDivisions: initDivisions || [],
                    selectedPeople: initPeople || [],
                    isPeopleOpen: false,
                    get availablePeople() {
                        var list = [];
                        for (var code in this.extDivisions) {
                            if (this.selectedDivisions.includes(code)) {
                                var members = this.extDivisions[code].members || {};
                                for (var name in members) {
                                    list.push({ name: name, role: members[name], code: code });
                                }
                            }
                        }
                        return list;
                    },
                    handleDivChange: function(selected) {
                        var self = this;
                        var removed = this.selectedDivisions.filter(function(d) { return !selected.includes(d); });
                        removed.forEach(function(d) {
                            var members = Object.keys(self.extDivisions[d] && self.extDivisions[d].members || {});
                            self.selectedPeople = self.selectedPeople.filter(function(p) { return !members.includes(p); });
                        });
                        this.selectedDivisions = selected;
                    },
                    togglePerson: function(name) {
                        if (this.selectedPeople.includes(name)) {
                            this.selectedPeople = this.selectedPeople.filter(function(x) { return x !== name; });
                        } else {
                            this.selectedPeople.push(name);
                        }
                    }
                };
            }
            </script>

            <form method="GET" action="{{ route('kalender.index') }}">
                <input type="hidden" name="divisi" value="{{ $divisi }}">
                <input type="hidden" name="year"   value="{{ $year }}">
                <input type="hidden" name="month"  value="{{ $month }}">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" id="filterTanggal" class="form-control form-control-sm"
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
                        <label class="form-label">PIC Internal</label>
                        @include('partials.pic-dropdown', [
                            'name'     => 'pic_internal',
                            'options'  => \App\Models\Meeting::$picOptions,
                            'labels'   => \App\Models\Meeting::$picLabels,
                            'selected' => array_filter(array_map('trim', explode(',', request('pic_internal', '')))),
                        ])
                    </div>

                    {{-- Divisi Eksternal --}}
                    <div class="col-md-3 col-sm-6"
                         x-data="kalExtDivPicker(_kalFiltExtDivs, _kalFiltInitDivs)">
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
                        <input type="hidden" name="divisi_ext" :value="selected.join(',')">
                    </div>

                    {{-- PIC Eksternal (muncul setelah divisi dipilih) --}}
                    <div class="col-md-3 col-sm-6"
                         x-data="kalExtPeoplePicker(_kalFiltExtDivs, _kalFiltInitDivs, _kalFiltInitPeople)"
                         @kal-ext-div-changed.window="handleDivChange($event.detail.selected)"
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
                            @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ request('ruangan_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-sm px-4"
                            style="background:#3b5bdb;border-color:#3b5bdb;color:#fff;font-weight:600">Terapkan</button>
                    <a href="{{ route('kalender.index', ['divisi'=>$divisi,'year'=>$year,'month'=>$month]) }}"
                       class="btn btn-sm px-4"
                       style="background:#e03131;border-color:#e03131;color:#fff;font-weight:600">Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Month navigator --}}
<div class="card mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <a href="{{ route('kalender.index', ['divisi'=>$divisi,'year'=>$prevMonth->year,'month'=>$prevMonth->month]) }}"
               class="btn btn-sm btn-outline-secondary flex-shrink-0">
                <i class="bi bi-chevron-left"></i>
            </a>
            <form method="GET" action="{{ route('kalender.index') }}" id="kalNavForm"
                  class="d-flex align-items-center gap-2">
                <input type="hidden" name="divisi" value="{{ $divisi }}">
                <select name="month" id="kalMonth" class="form-select form-select-sm"
                        style="width:auto;font-weight:600;color:#1e2847">
                    @foreach($monthNames as $num => $name)
                    <option value="{{ $num }}" {{ $month===$num?'selected':'' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="year" id="kalYear" class="form-select form-select-sm"
                        style="width:auto;font-weight:600;color:#1e2847">
                    @for($y=2015;$y<=2035;$y++)
                    <option value="{{ $y }}" {{ $year===$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
                @if($year!==now()->year||$month!==now()->month)
                <a href="{{ route('kalender.index', ['divisi'=>$divisi]) }}"
                   class="btn btn-sm btn-outline-secondary flex-shrink-0" style="font-size:12px">Bulan ini</a>
                @endif
            </form>
            <a href="{{ route('kalender.index', ['divisi'=>$divisi,'year'=>$nextMonth->year,'month'=>$nextMonth->month]) }}"
               class="btn btn-sm btn-outline-secondary flex-shrink-0">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Calendar --}}
<div class="card mb-4" style="overflow:hidden">
    <div class="cal-grid">
        @foreach($dayNames as $dn)
        <div class="cal-day-hdr">{{ $dn }}</div>
        @endforeach
    </div>
    <div class="cal-grid" style="border-left:1px solid #e2e8f0">
        @for($i=0;$i<$startOffset;$i++)
        <div class="cal-cell empty"></div>
        @endfor

        @for($d=1;$d<=$daysInMonth;$d++)
        @php
            $dk          = sprintf('%04d-%02d-%02d',$year,$month,$d);
            $dayMeetings = $meetings->get($dk,collect());
            $isToday     = $dk===$todayKey;
            $total       = $dayMeetings->count();
            $statusCount = $dayMeetings->groupBy('status')->map->count();
        @endphp
        <div class="cal-cell {{ $isToday?'today-cell':'' }} {{ $total>0?'has-meetings':'' }}"
             id="cal-{{ $dk }}"
             @if($total>0) onclick="filterByDate('{{ $dk }}')" title="{{ $total }} meeting — klik untuk filter" @endif>
            <div class="cal-day-num {{ $isToday?'today':'' }}">{{ $d }}</div>
            @if($total>0)
            <div class="cal-count">{{ $total }} meeting</div>
            <div class="cal-status-summary">
                @foreach($statusColors as $status=>$color)
                @if(($statusCount[$status]??0)>0)
                <div class="cal-status-line" style="color:{{ $color }}">{{ $status }} ({{ $statusCount[$status] }})</div>
                @endif
                @endforeach
            </div>
            @endif
        </div>
        @endfor

        @php
            $total=$startOffset+$daysInMonth; $rem=$total%7; $trailing=$rem===0?0:7-$rem;
        @endphp
        @for($i=0;$i<$trailing;$i++)
        <div class="cal-cell empty"></div>
        @endfor
    </div>
</div>

{{-- Meeting list table --}}
<div class="card">
    <div class="card-hdr">
        <span>
            <i class="bi bi-list-ul me-2" style="color:var(--teal)"></i>
            Daftar Meeting — <span id="tableMonthLabel">{{ $monthNames[$month] }} {{ $year }}</span>
        </span>
        <div class="d-flex align-items-center gap-3">
            <button id="copyWaBtn" onclick="copyWhatsAppMessage()"
                    class="btn btn-sm d-flex align-items-center gap-1"
                    style="background:#25d366;border:none;color:#fff;font-size:12px;font-weight:600;padding:4px 13px;border-radius:5px">
                <i class="bi bi-whatsapp"></i> Copy Pesan WA
            </button>
            <span id="meetingCount" style="font-size:12px;font-weight:600;color:var(--teal)">
                {{ $tableData->count() }} meeting
            </span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="meetingTable">
            <thead>
                <tr>
                    <th style="width:38px">#</th>
                    <th style="width:130px">ID</th>
                    <th style="min-width:110px">Tanggal</th>
                    <th>Kegiatan</th>
                    <th style="min-width:130px">Topik</th>
                    <th style="min-width:100px">Jam</th>
                    <th style="min-width:110px">Ruangan</th>
                    <th style="min-width:140px">PIC Internal</th>
                    <th style="min-width:120px">PIC External</th>
                    <th style="min-width:80px">Divisi</th>
                    <th style="min-width:90px">Status</th>
                    <th style="width:90px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tableData as $idx => $m)
                @php
                    $jamMulai   = \Carbon\Carbon::parse($m->jam_mulai)->format('H:i');
                    $jamSelesai = \Carbon\Carbon::parse($m->jam_selesai)->format('H:i');
                    $hariNames  = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
                    $hariStr    = $hariNames[$m->tanggal->dayOfWeek];
                    $meetingJson = json_encode([
                        'id'             => $m->id,
                        'meeting_code'   => $m->meeting_code ?? '',
                        'kegiatan'       => $m->kegiatan,
                        'tanggal'        => $m->tanggal->format('d M Y'),
                        'jam_mulai'      => $jamMulai,
                        'jam_selesai'    => $jamSelesai,
                        'kategori'       => $m->kategori,
                        'kategori_color' => $m->kategori_color,
                        'status'         => $m->status ?? 'To Do',
                        'status_badge'   => $m->status_badge,
                        'pic_internal'   => $m->pic_internal ?? '',
                        'pic_external'   => $m->pic_external ?? '',
                        'topik'          => $m->topic?->nama ?? '',
                        'ruangan'        => $m->ruangan ? $m->ruangan->nama.($m->ruangan->lokasi?' · '.$m->ruangan->lokasi:'') : '',
                        'hasil'          => $m->hasil ?? '',
                        'nm_file'        => $m->nm_file ?? '',
                        'link_nm'        => $m->link_nm ?? '',
                    ]);
                @endphp
                <tr class="meeting-row"
                    data-date="{{ $m->tanggal->format('Y-m-d') }}"
                    data-kategori="{{ strtolower($m->kategori) }}"
                    data-status="{{ strtolower($m->status ?? '') }}"
                    data-jam-mulai="{{ $jamMulai }}"
                    data-pic-internal="{{ strtolower($m->pic_internal ?? '') }}"
                    data-pic-external="{{ strtolower($m->pic_external ?? '') }}"
                    data-ruangan-id="{{ $m->ruangan_id ?? '' }}"
                    data-meeting="{{ $meetingJson }}">
                    <td class="row-num">{{ $idx + 1 }}</td>
                    <td>
                        <span style="font-family:monospace;font-size:11.5px;color:#6b7280;letter-spacing:0.3px">
                            {{ $m->meeting_code ?? '-' }}
                        </span>
                    </td>
                    <td style="white-space:nowrap">
                        <div style="font-weight:600;font-size:12.5px">{{ $m->tanggal->format('d M Y') }}</div>
                        <div style="font-size:10.5px;color:#9ca3af">{{ $hariStr }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:12.5px">{{ $m->kegiatan }}</div>
                    </td>
                    <td style="font-size:12px;color:#374151">{{ $m->topic?->nama ?? '–' }}</td>
                    <td style="font-size:12px;white-space:nowrap">{{ $jamMulai }} – {{ $jamSelesai }}</td>
                    <td style="font-size:12px">{{ $m->ruangan?->nama ?? '–' }}</td>
                    <td>
                        @if($m->pic_internal)
                        @php $pics = array_values(array_filter(array_map('trim', explode(',', $m->pic_internal)))); @endphp
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
                        @if($m->pic_external)
                        @php $picsExt = array_values(array_filter(array_map('trim', explode(',', $m->pic_external)))); @endphp
                        @foreach($picsExt as $i => $pe)
                            @if($i >= 5) @break @endif
                            <span class="pic-chip">{{ \App\Models\Meeting::externalPicLabel($pe) }}</span>
                        @endforeach
                        @if(count($picsExt) > 5)
                            <span class="pic-chip" style="background:#e2e8f0;color:#6b7280;font-weight:700">+{{ count($picsExt) - 5 }}</span>
                        @endif
                        @else
                        <span style="color:#9ca3af">-</span>
                        @endif
                    </td>
                    <td>
                        @php $divCodes = $m->pic_external ? \App\Models\Meeting::externalPicDivisions($m->pic_external) : []; @endphp
                        @forelse($divCodes as $dc)
                            <span class="pic-chip" style="background:#eef2ff;color:#4361ee;font-weight:700">{{ $dc }}</span>
                        @empty
                            <span style="color:#9ca3af">–</span>
                        @endforelse
                    </td>
                    <td><span class="badge badge-status bg-{{ $m->status_badge }}">{{ $m->status }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary btn-aksi"
                                onclick="event.stopPropagation(); showMeetingDetail(this.closest('tr').closest('tr'))"
                                title="Lihat detail">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-aksi btn-copy-row"
                                style="background:#25d366;border:none;color:#fff"
                                onclick="event.stopPropagation(); copyRowWA(this.closest('tr'))"
                                title="Copy pesan WA">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="no-results-row">
                    <td colspan="12" class="text-center">Tidak ada meeting pada bulan ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="meetingDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span id="mdlCode" style="font-family:monospace;font-size:12px;color:#6b7280"></span>
                    <span id="mdlStatus" class="badge badge-status"></span>
                    <span id="mdlKategori" class="badge rounded-pill" style="font-size:10.5px;font-weight:600;padding:4px 10px"></span>
                </div>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:16px 20px">
                <div style="font-size:14px;font-weight:600;color:#1e2847;margin-bottom:12px" id="mdlKegiatan"></div>
                <div style="display:flex;gap:24px;flex-wrap:wrap;padding:9px 13px;background:#f8fafc;border-radius:7px;margin-bottom:12px">
                    <div>
                        <div class="detail-label" style="margin-bottom:2px">Tanggal</div>
                        <div class="detail-val"><i class="bi bi-calendar3 me-1" style="color:var(--teal)"></i><span id="mdlTanggal"></span></div>
                    </div>
                    <div>
                        <div class="detail-label" style="margin-bottom:2px">Jam</div>
                        <div class="detail-val"><i class="bi bi-clock me-1" style="color:var(--teal)"></i><span id="mdlJam"></span></div>
                    </div>
                    <div id="mdlRuanganWrap">
                        <div class="detail-label" style="margin-bottom:2px">Ruangan</div>
                        <div class="detail-val"><i class="bi bi-door-open me-1" style="color:var(--teal)"></i><span id="mdlRuangan"></span></div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;border:1px solid #e2e8f0;border-radius:7px;overflow:hidden;margin-bottom:12px">
                    <div style="padding:9px 13px;border-right:1px solid #e2e8f0">
                        <div class="detail-label" style="margin-bottom:5px">PIC Internal</div>
                        <div id="mdlPicInternal"></div>
                    </div>
                    <div style="padding:9px 13px">
                        <div class="detail-label" style="margin-bottom:5px">PIC Eksternal</div>
                        <div id="mdlPicExternal"></div>
                    </div>
                </div>
                <div id="mdlNmRow">
                    <div class="detail-label" style="margin-bottom:5px">Notula Meeting</div>
                    <div id="mdlNm"></div>
                </div>
                <div id="mdlHasilRow" style="margin-top:10px;display:none">
                    <div class="detail-label" style="margin-bottom:5px">Hasil / Catatan</div>
                    <div id="mdlHasil" style="font-size:13px;color:#374151;white-space:pre-line"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <a id="mdlDetailLink" href="#" class="btn btn-sm btn-teal">
                    <i class="bi bi-arrow-up-right-square me-1"></i>Lihat Detail Lengkap
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Copy WA --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="waToast" class="toast align-items-center border-0 shadow" role="alert"
         style="background:#fff;min-width:280px;border-radius:10px">
        <div class="d-flex align-items-center gap-3 px-3 py-3">
            <div style="width:34px;height:34px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-whatsapp" style="color:#25d366;font-size:17px"></i>
            </div>
            <div id="waToastLabel" style="font-size:13px;color:#1f2937;font-weight:500"></div>
        </div>
        <div style="height:3px;background:#25d366;border-radius:0 0 10px 10px"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Filter accordion icon ──
const kalFilterArea = document.getElementById('kalFilterArea')
const kalFilterIcon = document.getElementById('kalFilterIcon')
if (kalFilterArea && kalFilterIcon) {
    kalFilterArea.addEventListener('show.bs.collapse', () => kalFilterIcon.className = 'bi bi-dash-lg')
    kalFilterArea.addEventListener('hide.bs.collapse', () => kalFilterIcon.className = 'bi bi-plus-lg')
    if (kalFilterArea.classList.contains('show')) kalFilterIcon.className = 'bi bi-dash-lg'
}

// ── Month/year picker ──
let _kalNavTimer
const _kalMonthEl = document.getElementById('kalMonth')
const _kalYearEl  = document.getElementById('kalYear')
if (_kalMonthEl) _kalMonthEl.addEventListener('change', () => {
    clearTimeout(_kalNavTimer)
    _kalNavTimer = setTimeout(() => document.getElementById('kalNavForm').submit(), 120)
})
if (_kalYearEl) _kalYearEl.addEventListener('change', () => {
    clearTimeout(_kalNavTimer)
    _kalNavTimer = setTimeout(() => document.getElementById('kalNavForm').submit(), 120)
})

// ── Klik tanggal di kalender → scroll ke tabel ──
function filterByDate(dateKey) {
    document.getElementById('meetingTable').closest('.card').scrollIntoView({ behavior: 'smooth', block: 'start' })
}

// ── Meeting detail modal (lazy init) ──
let _detailModal = null
function _getDetailModal() {
    if (!_detailModal) _detailModal = new bootstrap.Modal(document.getElementById('meetingDetailModal'))
    return _detailModal
}

function emptyVal() { return '<span style="color:#9ca3af">-</span>' }

function showMeetingDetail(rowEl) {
    const m = JSON.parse(rowEl.dataset.meeting)
    document.getElementById('mdlCode').textContent     = m.meeting_code || ''
    document.getElementById('mdlKegiatan').textContent = m.kegiatan
    const statusEl = document.getElementById('mdlStatus')
    statusEl.textContent = m.status
    statusEl.className   = 'badge badge-status bg-' + (m.status_badge || 'secondary')
    const katEl = document.getElementById('mdlKategori')
    katEl.textContent      = m.kategori
    katEl.style.background = m.kategori_color
    document.getElementById('mdlTanggal').textContent = m.tanggal
    document.getElementById('mdlJam').textContent     = m.jam_mulai + ' – ' + m.jam_selesai
    const ruanganWrap = document.getElementById('mdlRuanganWrap')
    if (m.ruangan) { document.getElementById('mdlRuangan').textContent = m.ruangan; ruanganWrap.style.display = '' }
    else ruanganWrap.style.display = 'none'
    const PRM = window._KAL_PIC_ROLE_MAP || {}
    const EXD = window._KAL_EXT_DIVISIONS || {}
    document.getElementById('mdlPicInternal').innerHTML = m.pic_internal
        ? m.pic_internal.split(',').map(function(p) {
            p = p.trim()
            var info = PRM[p.toLowerCase()]
            var lbl  = info ? p + ' – ' + info.role : p
            return '<div style="font-size:12.5px;color:#1e2847;padding:2px 0;border-bottom:1px solid #f1f5f9;display:flex;gap:6px">'
                 + '<span style="color:#29b4d0;flex-shrink:0">•</span><span>' + lbl + '</span></div>'
          }).join('') : emptyVal()
    document.getElementById('mdlPicExternal').innerHTML = m.pic_external
        ? m.pic_external.split(',').map(function(p) {
            p = p.trim(); var lbl = p
            for (var code in EXD) { if (EXD[code].members && EXD[code].members[p]) { lbl = p + ' – ' + EXD[code].members[p]; break } }
            return '<div style="font-size:12.5px;color:#1e2847;padding:2px 0;border-bottom:1px solid #f1f5f9;display:flex;gap:6px">'
                 + '<span style="color:#4361ee;flex-shrink:0">•</span><span>' + lbl + '</span></div>'
          }).join('') : emptyVal()
    var nmEl = document.getElementById('mdlNm')
    if (m.nm_file) nmEl.innerHTML = '<a href="/' + m.nm_file + '" target="_blank" class="btn btn-sm btn-outline-success" style="font-size:12px"><i class="bi bi-file-earmark-check me-1"></i>Lihat File NM</a>'
    else if (m.link_nm) nmEl.innerHTML = '<a href="' + m.link_nm + '" target="_blank" class="btn btn-sm btn-outline-info" style="font-size:12px"><i class="bi bi-link-45deg me-1"></i>Buka Link NM</a>'
    else nmEl.innerHTML = '<span style="color:#9ca3af">-</span>'
    var hasilRow = document.getElementById('mdlHasilRow')
    if (m.hasil) { document.getElementById('mdlHasil').textContent = m.hasil; hasilRow.style.display = '' }
    else hasilRow.style.display = 'none'
    document.getElementById('mdlDetailLink').href = '/agenda/' + m.id
    _getDetailModal().show()
}

// ── Copy WA ──
window._KAL_PIC_ROLE_MAP  = @json($picRoleMap);
window._KAL_EXT_DIVISIONS = @json(\App\Models\Meeting::$externalDivisions);

var _KAL_MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
var _KAL_DAYS   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

function getHeaderPeriod() {
    var filterEl = document.getElementById('filterTanggal');
    var dateVal  = filterEl ? filterEl.value : '';
    if (dateVal) {
        var d = new Date(dateVal + 'T00:00:00');
        return _KAL_DAYS[d.getDay()] + ', ' + d.getDate() + ' ' + _KAL_MONTHS[d.getMonth()] + ' ' + d.getFullYear();
    }
    var labelEl = document.getElementById('tableMonthLabel');
    return labelEl ? labelEl.textContent.trim() : '';
}

function formatMeetingBlock(m, num) {
    var jam = m.jam_mulai.replace(':', '.');
    var sp  = '        ';
    var keg = m.topik
        ? 'Meeting ' + m.kategori + ' - ' + m.topik + ' - ' + m.kegiatan
        : 'Meeting ' + m.kategori + ' - ' + m.kegiatan;
    var block = '*' + num + '. Jam ' + jam + '*\n';
    block += sp + 'a. Kegiatan : ' + keg + '\n';
    block += sp + 'b. Status : ' + m.status + '\n';
    block += sp + 'c. PIC Internal : ' + (m.pic_internal || '-') + '\n';
    if (m.pic_external) {
        block += sp + 'd. PIC External : ' + m.pic_external + '\n';
        block += sp + 'e. Link NM : ' + (m.link_nm || '') + '\n';
    } else {
        block += sp + 'd. Link NM : ' + (m.link_nm || '') + '\n';
    }
    return block;
}

// Bangun body teks dari rows (dikelompokkan per tanggal jika tidak ada filter tanggal)
function _buildBody(rows) {
    var filterEl = document.getElementById('filterTanggal');
    var byDate = {}, num = 1, body = '';
    if (filterEl && filterEl.value) {
        rows.forEach(function(row) {
            body += formatMeetingBlock(JSON.parse(row.dataset.meeting), num++) + '\n';
        });
    } else {
        rows.forEach(function(row) {
            var dk = row.dataset.date;
            if (!byDate[dk]) byDate[dk] = [];
            byDate[dk].push(row);
        });
        Object.keys(byDate).sort().forEach(function(dk, idx) {
            var d = new Date(dk + 'T00:00:00');
            var lbl = _KAL_DAYS[d.getDay()] + ', ' + d.getDate() + ' ' + _KAL_MONTHS[d.getMonth()] + ' ' + d.getFullYear();
            if (idx > 0) body += '\n';
            body += '_' + lbl + '_\n\n';
            byDate[dk].forEach(function(row) {
                body += formatMeetingBlock(JSON.parse(row.dataset.meeting), num++) + '\n';
            });
        });
    }
    return body;
}

// Bangun satu blok WA untuk satu PIC (atau null untuk header generik)
function _buildMsg(picName, rows) {
    var period = getHeaderPeriod();
    var line1  = '*AGENDA IT ' + period + '*';
    var line2  = '*Agenda Meeting*';
    if (picName) {
        var PRM  = window._KAL_PIC_ROLE_MAP || {};
        var info = PRM[picName.toLowerCase()];
        if (info) line2 = '*Agenda ' + info.role + ' (' + info.fullName + ')*';
    }
    return line1 + '\n' + line2 + '\n\n' + _buildBody(rows);
}

// Cek apakah picName ada di string pic_internal sebuah row
function _picInRow(picName, rowPicStr) {
    var pLow = picName.toLowerCase();
    return (rowPicStr || '').toLowerCase().split(',').some(function(p) {
        return p.trim() === pLow;
    });
}

function _doCopy(text, onDone) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.cssText = 'position:absolute;left:-9999px;top:0;';
    document.body.appendChild(ta);
    ta.select();
    var ok = false;
    try { ok = document.execCommand('copy'); } catch(e) {}
    document.body.removeChild(ta);
    if (ok) { onDone(); return; }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(onDone).catch(function() {
            alert('Copy gagal. Coba di browser lain.');
        });
    } else {
        alert('Copy tidak didukung browser ini.');
    }
}

function _showCopyToast(msg) {
    var el = document.getElementById('waToast');
    if (!el) { alert(msg); return; }
    el.querySelector('#waToastLabel').textContent = msg;
    bootstrap.Toast.getOrCreateInstance(el, { delay: 2500, autohide: true }).show();
}

function copyWhatsAppMessage() {
    var rows = Array.prototype.slice.call(document.querySelectorAll('.meeting-row'))
        .filter(function(r) { return r.style.display !== 'none'; });
    if (rows.length === 0) { alert('Tidak ada data meeting untuk disalin.'); return; }

    // Baca filter PIC dari URL
    var params    = new URLSearchParams(window.location.search);
    var picFilter = (params.get('pic_internal') || '').trim();
    var pics      = picFilter
        ? picFilter.split(',').map(function(p) { return p.trim(); }).filter(Boolean)
        : [];

    var message = '';
    if (pics.length === 0) {
        // Tidak ada filter PIC → satu blok generik
        message = _buildMsg(null, rows);
    } else if (pics.length === 1) {
        // Satu PIC → satu blok dengan header PIC
        message = _buildMsg(pics[0], rows);
    } else {
        // Banyak PIC → satu blok per PIC, hanya baris yang ada PIC tersebut
        var blocks = pics.map(function(pic) {
            var picRows = rows.filter(function(row) {
                return _picInRow(pic, row.dataset.picInternal);
            });
            return _buildMsg(pic, picRows.length > 0 ? picRows : rows);
        });
        message = blocks.join('\n\n');
    }

    _doCopy(message, function() { _showCopyToast(rows.length + ' meeting berhasil disalin!'); });
}

function copyRowWA(row) {
    var m   = JSON.parse(row.dataset.meeting);
    var d   = new Date(row.dataset.date + 'T00:00:00');
    var lbl = _KAL_DAYS[d.getDay()] + ', ' + d.getDate() + ' ' + _KAL_MONTHS[d.getMonth()] + ' ' + d.getFullYear();
    _doCopy('*AGENDA IT ' + lbl + '*\n\n' + formatMeetingBlock(m, 1), function() {
        _showCopyToast('Pesan WA meeting berhasil disalin!');
    });
}
</script>
@endpush
