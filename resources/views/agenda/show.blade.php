@extends('layouts.app')

@section('title', 'Detail Agenda Meeting')
@section('breadcrumb', 'IT > Agenda Meeting > Detail')

@section('content')

@php $canAct = in_array(session('sim_user', \App\Models\Meeting::$accounts[0])['role'], \App\Models\Meeting::$managerRoles); @endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <a href="{{ route('agenda.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>

    @if($canAct && $agenda->status !== 'Cancelled')
    <div class="d-flex gap-2">
        {{-- Reschedule --}}
        @if(!in_array($agenda->status, ['Done']))
        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
            <i class="bi bi-arrow-repeat me-1"></i>Reschedule
        </button>
        @endif

        {{-- Edit --}}
        <a href="{{ route('agenda.edit', $agenda) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
    </div>
    @endif
</div>

<div class="card">
    {{-- Header bar --}}
    <div class="card-hdr">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span style="font-family:monospace;font-size:12px;color:#6b7280">{{ $agenda->meeting_code ?? '—' }}</span>
            <span class="badge badge-status bg-{{ $agenda->status_badge }}">{{ $agenda->status }}</span>
            @if($agenda->status === 'Cancelled')
            <span style="font-size:11px;color:#dc2626;font-weight:600">
                <i class="bi bi-slash-circle me-1"></i>Meeting ini telah dibatalkan
            </span>
            @endif
        </div>
        <span class="badge rounded-pill"
              style="background:{{ $agenda->kategori_color }};font-size:10.5px;font-weight:600;padding:4px 10px">
            {{ $agenda->kategori }}
        </span>
    </div>

    <div class="card-body" style="padding:16px 20px">

        {{-- ① Kegiatan --}}
        <div style="margin-bottom:14px">
            <div class="detail-label">Kegiatan</div>
            <div style="font-size:14px;font-weight:600;color:#1e2847;line-height:1.5">{{ $agenda->kegiatan }}</div>
        </div>

        {{-- ② Tanggal · Jam · Ruangan --}}
        <div style="display:flex;gap:32px;flex-wrap:wrap;padding:10px 14px;background:#f8fafc;border-radius:7px;margin-bottom:14px">
            <div>
                <div class="detail-label" style="margin-bottom:3px">Tanggal</div>
                <div class="detail-val">
                    <i class="bi bi-calendar3 me-1" style="color:var(--teal)"></i>
                    {{ $agenda->tanggal->format('d M Y') }}
                </div>
            </div>
            <div>
                <div class="detail-label" style="margin-bottom:3px">Jam</div>
                <div class="detail-val">
                    <i class="bi bi-clock me-1" style="color:var(--teal)"></i>
                    {{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i') }}
                </div>
            </div>
            <div>
                <div class="detail-label" style="margin-bottom:3px">Ruangan</div>
                <div class="detail-val">
                    @if($agenda->ruangan)
                        <i class="bi bi-door-open me-1" style="color:var(--teal)"></i>
                        {{ $agenda->ruangan->nama }}
                        @if($agenda->ruangan->lokasi)
                        <span style="font-size:11px;color:#9ca3af;font-weight:400"> · {{ $agenda->ruangan->lokasi }}</span>
                        @endif
                    @else
                        <span class="detail-empty">Tidak diisi</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ③ PIC --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;border:1px solid #e2e8f0;border-radius:7px;overflow:hidden;margin-bottom:14px">
            <div style="padding:10px 14px;border-right:1px solid #e2e8f0">
                <div class="detail-label" style="margin-bottom:8px">PIC Internal</div>
                @if($agenda->pic_internal)
                    @foreach(array_filter(array_map('trim', explode(',', $agenda->pic_internal))) as $pic)
                    <div style="font-size:12.5px;color:#1e2847;padding:3px 0;display:flex;gap:6px;align-items:flex-start;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
                        <span style="color:#29b4d0;flex-shrink:0">•</span><span>{{ \App\Models\Meeting::$picLabels[$pic] ?? $pic }}</span>
                    </div>
                    @endforeach
                @else
                    <span class="detail-empty">Tidak diisi</span>
                @endif
            </div>
            <div style="padding:10px 14px">
                <div class="detail-label" style="margin-bottom:8px">PIC Eksternal</div>
                @if($agenda->pic_external)
                    @foreach(array_filter(array_map('trim', explode(',', $agenda->pic_external))) as $picE)
                    <div style="font-size:12.5px;color:#1e2847;padding:3px 0;display:flex;gap:6px;align-items:flex-start;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
                        <span style="color:#4361ee;flex-shrink:0">•</span><span>{{ $picE }}</span>
                    </div>
                    @endforeach
                @else
                    <span class="detail-empty">Tidak diisi</span>
                @endif
            </div>
        </div>

        {{-- ④ Notula + Hasil (sejajar kalau ada keduanya) --}}
        <div style="display:grid;grid-template-columns:{{ $agenda->hasil ? '1fr 1fr' : '1fr' }};gap:16px;margin-bottom:{{ (!empty($agenda->reschedule_history)) ? '14px' : '0' }}">
            <div>
                <div class="detail-label" style="margin-bottom:5px">Notula Meeting</div>
                @if($agenda->nm_file)
                    <a href="{{ asset($agenda->nm_file) }}" target="_blank"
                       class="btn btn-sm btn-outline-success" style="font-size:12px">
                        <i class="bi bi-file-earmark-check me-1"></i>Lihat File NM
                    </a>
                @elseif($agenda->link_nm)
                    <a href="{{ $agenda->link_nm }}" target="_blank"
                       class="btn btn-sm btn-outline-info" style="font-size:12px">
                        <i class="bi bi-link-45deg me-1"></i>Buka Link NM
                    </a>
                @else
                    <span class="detail-empty">Belum ada file</span>
                @endif
            </div>
            @if($agenda->hasil)
            <div>
                <div class="detail-label" style="margin-bottom:5px">Hasil / Catatan</div>
                <div style="font-size:13px;color:#374151;white-space:pre-line">{{ $agenda->hasil }}</div>
            </div>
            @endif
        </div>

        {{-- ⑤ Riwayat Reschedule --}}
        @if(!empty($agenda->reschedule_history))
        <div style="border-top:1px solid #e2e8f0;padding-top:12px">
            <div class="detail-label" style="margin-bottom:8px">
                <i class="bi bi-clock-history me-1" style="color:#f59e0b"></i>
                Riwayat Reschedule ({{ count($agenda->reschedule_history) }}x)
            </div>
            <div style="border:1px solid #fde68a;border-radius:7px;overflow:hidden">
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
                <div style="padding:9px 13px;{{ $i > 0 ? 'border-top:1px solid #fde68a;' : '' }}background:{{ $i === 0 ? '#fffbeb' : '#fff' }}">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <span style="font-size:11px;font-weight:700;color:#92400e">#{{ count($agenda->reschedule_history) - $i }}</span>
                            <span style="font-size:12px;color:#374151;margin-left:8px">
                                <span style="text-decoration:line-through;color:#9ca3af">
                                    {{ \Carbon\Carbon::parse($h['dari_tanggal'])->format('d-m-Y') }} {{ $dariJam }}
                                </span>
                                <i class="bi bi-arrow-right mx-1" style="color:#f59e0b;font-size:11px"></i>
                                <strong>{{ \Carbon\Carbon::parse($h['ke_tanggal'])->format('d-m-Y') }} {{ $keJam }}</strong>
                            </span>
                            @if(!empty($h['alasan']))
                            <div style="font-size:11.5px;color:#6b7280;margin-top:2px">
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

        {{-- Meta --}}
        <div style="border-top:1px solid #e2e8f0;margin-top:14px;padding-top:10px">
            <small class="text-muted">
                Dibuat: {{ $agenda->created_at->format('d-m-Y H:i') }}
                &nbsp;·&nbsp;
                Diperbarui: {{ $agenda->updated_at->format('d-m-Y H:i') }}
            </small>
        </div>

    </div>
</div>

{{-- Reschedule Modal --}}
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">
                    <i class="bi bi-arrow-repeat me-2" style="color:#f59e0b"></i>Reschedule Meeting
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('agenda.reschedule', $agenda) }}">
                @csrf
                <div class="modal-body py-3">
                    <div class="alert alert-warning py-2 mb-3" style="font-size:11.5px">
                        <i class="bi bi-info-circle me-1"></i>
                        Jadwal lama: <strong>{{ $agenda->tanggal->format('d M Y') }} · {{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i') }}</strong>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Tanggal Baru <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_baru" class="form-control form-control-sm"
                               value="{{ $agenda->tanggal->format('Y-m-d') }}" required>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai_baru" class="form-control form-control-sm"
                                   value="{{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai_baru" class="form-control form-control-sm"
                                   value="{{ \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i') }}" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Ruangan</label>
                        <select name="ruangan_id" class="form-select form-select-sm">
                            <option value="">— Pilih Ruangan —</option>
                            @foreach(\App\Models\Room::orderBy('nama')->get() as $r)
                            <option value="{{ $r->id }}" {{ $agenda->ruangan_id == $r->id ? 'selected' : '' }}>
                                {{ $r->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">PIC Internal</label>
                        @include('partials.pic-dropdown', [
                            'name'     => 'pic_internal',
                            'options'  => \App\Models\Meeting::$picOptions,
                            'labels'   => \App\Models\Meeting::$picLabels,
                            'selected' => array_filter(array_map('trim', explode(',', $agenda->pic_internal ?? ''))),
                        ])
                    </div>

                    <div class="mb-2">
                        <label class="form-label">PIC Eksternal</label>
                        @include('partials.pic-external-dropdown', [
                            'name'     => 'pic_external',
                            'selected' => array_filter(array_map('trim', explode(',', $agenda->pic_external ?? ''))),
                        ])
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Alasan <span class="text-muted" style="font-size:11px">(opsional)</span></label>
                        <input type="text" name="alasan" class="form-control form-control-sm"
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

@endsection

