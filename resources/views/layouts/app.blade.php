<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Agenda Meeting') | D&apos; PARAGON</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-bg: #ffffff;
            --sidebar-border: #e8ecf0;
            --sidebar-hover: rgba(41, 180, 208, 0.08);
            --sidebar-active-bg: rgba(41, 180, 208, 0.12);
            --teal: #29b4d0;
            --teal-dark: #1f9ab3;
            --body-bg: #f0f4f8;
            --dark: #231f20;
            --border: #e2e8f0;
            --table-head: #1e2847;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--body-bg);
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 13px;
            color: #374151;
        }

        /* ── SIDEBAR ── */
        #sidebar {
            width: 220px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }

        .sidebar-logo {
            padding: 14px 18px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-grid {
            width: 26px; height: 26px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px;
            flex-shrink: 0;
        }
        .logo-grid div { border-radius: 1px; }
        .lg-1 { background: #231f20; }
        .lg-2 { background: #29b4d0; }
        .lg-3 { background: #29b4d0; }
        .lg-4 { background: #231f20; }

        .logo-text .brand { color: #1e2847; font-size: 13px; font-weight: 700; letter-spacing: 1px; }
        .logo-text .sub   { color: #9ca3af; font-size: 9.5px; }

        .sidebar-section {
            padding: 14px 18px 4px;
            font-size: 9.5px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .sidebar-nav { list-style: none; margin: 0; padding: 0; }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 18px;
            color: #4b5563;
            text-decoration: none;
            font-size: 12.5px;
            transition: all 0.15s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover { background: var(--sidebar-hover); color: #1e2847; }
        .sidebar-nav a.active { background: var(--sidebar-active-bg); color: var(--teal); border-left-color: var(--teal); font-weight: 600; }
        .sidebar-nav a i { width: 16px; font-size: 14px; flex-shrink: 0; }
        .sidebar-nav .arrow { margin-left: auto; font-size: 11px; transition: transform 0.2s; }
        .sidebar-nav a[aria-expanded="true"] .arrow { transform: rotate(180deg); }

        .sidebar-subnav { list-style: none; margin: 0; padding: 0; }
        .sidebar-subnav a { padding-left: 42px; font-size: 12px; }

        /* ── User / Simulasi area ── */
        .sidebar-user-area {
            margin-top: auto;
            border-top: 1px solid var(--sidebar-border);
            position: relative;
        }

        .sim-current {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .sim-current:hover { background: #f8fafc; }

        .user-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 11px; font-weight: 700;
            flex-shrink: 0;
        }

        .user-info .uname { color: #1e2847; font-size: 12px; font-weight: 600; }
        .user-info .urole { font-size: 9.5px; }

        .sim-dropdown {
            position: absolute;
            bottom: 100%;
            left: 0; right: 0;
            background: #fff;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.15);
            overflow: hidden;
            z-index: 2000;
        }

        .sim-dropdown .sim-hdr {
            padding: 10px 14px 6px;
            font-size: 9.5px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #f0f0f0;
        }

        .sim-dropdown button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 14px;
            border: none;
            background: transparent;
            text-align: left;
            transition: background 0.1s;
        }

        .sim-dropdown button:hover { background: #f8fafc; }
        .sim-dropdown button.active-user { background: #f0fbff; }

        .sim-avatar {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 10px; font-weight: 700;
            flex-shrink: 0;
        }

        .sim-name { font-size: 12px; font-weight: 600; color: #1f2937; line-height: 1.3; }
        .sim-role { font-size: 10px; color: #9ca3af; }

        .role-badge {
            font-size: 9.5px;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }

        /* ── MAIN ── */
        #main {
            margin-left: 220px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 10px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 900;
        }

        .topbar .breadcrumb-text { font-size: 12px; color: #9ca3af; }

        .content-wrap { padding: 20px 24px; flex: 1; }

        .page-hdr {
            background: #fff;
            border-radius: 8px;
            padding: 14px 20px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .page-hdr h4 { font-size: 15px; font-weight: 700; color: var(--dark); margin: 0; }

        .card { border: none; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }

        .card-hdr {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 12px 18px;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 8px 8px 0 0;
        }

        .table thead th {
            background: var(--table-head) !important;
            color: #fff !important;
            font-size: 11.5px; font-weight: 600;
            padding: 10px 12px;
            border: none !important;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 9px 12px;
            font-size: 12.5px;
            vertical-align: middle;
            border-color: #eef0f3;
        }

        .table tbody tr:hover { background: #f8fafc; }

        .badge-status { font-size: 10.5px; padding: 3px 9px; border-radius: 4px; font-weight: 600; }

        .btn-teal { background: var(--teal); border-color: var(--teal); color: #fff; font-size: 12px; }
        .btn-teal:hover { background: var(--teal-dark); border-color: var(--teal-dark); color: #fff; }

        .form-label { font-size: 12px; font-weight: 600; color: #4b5563; margin-bottom: 4px; }
        .form-control, .form-select { font-size: 12.5px; border-color: var(--border); border-radius: 5px; }
        .form-control:focus, .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 0.18rem rgba(41,180,208,0.22);
        }

        .alert { font-size: 12.5px; border-radius: 6px; }
        .pagination { font-size: 12px; }
        .page-link { color: var(--table-head); }
        .page-item.active .page-link { background: var(--table-head); border-color: var(--table-head); }

        /* PIC multi-select dropdown */
        .pic-dropdown { position: relative; }
        .pic-trigger {
            min-height: 36px;
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 4px 10px;
            cursor: pointer;
            background: #fff;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 3px;
            font-size: 12.5px;
        }
        .pic-trigger:focus-within { border-color: var(--teal); box-shadow: 0 0 0 0.18rem rgba(41,180,208,0.22); }
        .pic-list {
            position: absolute;
            top: 100%; left: 0; right: 0;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 999;
            max-height: 200px;
            overflow-y: auto;
        }
        .pic-list label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            cursor: pointer;
            font-size: 12.5px;
            border-bottom: 1px solid #f5f5f5;
        }
        .pic-list label:hover { background: #f8fafc; }
        .pic-chip {
            display: inline-block;
            background: #e8f4ff;
            color: #1e2847;
            border: 1px solid #93c5e8;
            border-radius: 3px;
            font-size: 10.5px;
            padding: 2px 6px;
            font-weight: 600;
            white-space: nowrap;
            margin: 1px 2px 1px 0;
            vertical-align: middle;
        }

        /* Calendar */
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
        .cal-day-hdr { padding: 10px 8px; text-align: center; color: #fff; font-size: 12px; font-weight: 600; background: var(--table-head); }
        .cal-cell { min-height: 90px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 6px; }
        .cal-cell.empty { background: #f8fafc; }
        .cal-day-num { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
        .cal-day-num.today { background: var(--teal); color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; }
        .cal-cell.today-cell { background: #f0fbff; }
        .cal-event { font-size: 10px; padding: 2px 5px; border-radius: 3px; margin-bottom: 2px; color: #fff; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; cursor: pointer; }

        .pic-error-wrap .pic-trigger { border-color: #dc2626 !important; }
        .cal-popover { min-width: 200px; max-width: 260px; }
        .cal-popover .popover-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 12px; font-size: 12px; }
        .cal-popover .popover-body { padding: 9px 12px; font-size: 12px; }

        .detail-label {
            font-size: 10.5px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .detail-val { font-size: 13px; font-weight: 600; color: #374151; }
        .detail-empty { font-size: 12px; color: #cbd5e1; font-style: italic; }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-220px); transition: transform 0.25s; }
            #sidebar.open { transform: translateX(0); }
            #main { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

@php $simUser = session('sim_user', \App\Models\Meeting::$accounts[0]); @endphp

{{-- ══ SIDEBAR ══ --}}
<aside id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-grid">
            <div class="lg-1"></div><div class="lg-2"></div>
            <div class="lg-3"></div><div class="lg-4"></div>
        </div>
        <div class="logo-text">
            <div class="brand">D&apos; PARAGON</div>
            <div class="sub">IT Management System</div>
        </div>
    </div>

    <div class="sidebar-section">IT</div>

    <ul class="sidebar-nav">
        <li>
            <a href="#"
               data-bs-toggle="collapse"
               data-bs-target="#menuAgenda"
               aria-expanded="{{ (request()->routeIs('agenda.*') || request()->routeIs('kalender.*')) ? 'true' : 'false' }}"
               class="{{ (request()->routeIs('agenda.*') || request()->routeIs('kalender.*')) ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i>
                Agenda Meeting
                <i class="bi bi-chevron-down arrow"></i>
            </a>
            <div id="menuAgenda" class="collapse {{ (request()->routeIs('agenda.*') || request()->routeIs('kalender.*')) ? 'show' : '' }}">
                <ul class="sidebar-subnav">
                    <li>
                        <a href="{{ route('agenda.index') }}" class="{{ request()->routeIs('agenda.index') || request()->routeIs('agenda.create') || request()->routeIs('agenda.edit') ? 'active' : '' }}">
                            <i class="bi bi-list-ul"></i>Agenda Meeting
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('kalender.index') }}" class="{{ request()->routeIs('kalender.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-month"></i>Kalender Meeting
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>

    <div class="sidebar-section">Master</div>

    <ul class="sidebar-nav">
        <li>
            <a href="{{ route('ruangan.index') }}"
               class="{{ request()->routeIs('ruangan.*') ? 'active' : '' }}">
                <i class="bi bi-door-open"></i>Ruangan
            </a>
        </li>
        <li>
            <a href="{{ route('topik.index') }}"
               class="{{ request()->routeIs('topik.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i>Topik
            </a>
        </li>
        <li>
            <a href="{{ route('konfigurasi-waktu.index') }}"
               class="{{ request()->routeIs('konfigurasi-waktu.*') ? 'active' : '' }}">
                <i class="bi bi-clock"></i>Konfigurasi Waktu
            </a>
        </li>
    </ul>

    {{-- ── Simulasi Login ── --}}
    <div class="sidebar-user-area" x-data="{ open: false }">

        {{-- Dropdown list --}}
        <div class="sim-dropdown" x-show="open" x-cloak @click.outside="open = false">
            <div class="sim-hdr">Simulasi Login</div>
            @foreach(\App\Models\Meeting::$accounts as $acc)
            <form method="POST" action="{{ route('simulasi.login') }}">
                @csrf
                <input type="hidden" name="name" value="{{ $acc['name'] }}">
                <button type="submit" class="{{ $simUser['name'] === $acc['name'] ? 'active-user' : '' }}">
                    <div class="sim-avatar" style="background:{{ $acc['color'] }}">{{ $acc['abbrev'] }}</div>
                    <div class="flex-1">
                        <div class="sim-name">{{ $acc['name'] }}</div>
                        <div class="sim-role">{{ $acc['jabatan'] ?? $acc['role'] }}</div>
                    </div>
                    @if($simUser['name'] === $acc['name'])
                        <i class="bi bi-check-lg" style="color:var(--teal);font-size:13px"></i>
                    @endif
                </button>
            </form>
            @endforeach
        </div>

        {{-- Current user display --}}
        <div class="sim-current" @click="open = !open">
            <div class="user-avatar" style="background:{{ $simUser['color'] }}">{{ $simUser['abbrev'] }}</div>
            <div class="user-info flex-1">
                <div class="uname">{{ $simUser['name'] }}</div>
                <div class="urole" style="color:{{ $simUser['color'] }}">{{ $simUser['jabatan'] ?? $simUser['role'] }}</div>
            </div>
            <i class="bi bi-chevron-up" style="color:#9ca3af;font-size:11px"
               :style="open ? '' : 'transform:rotate(180deg)'"></i>
        </div>
    </div>
</aside>

{{-- ══ MAIN ══ --}}
<div id="main">
    <header class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="bi bi-list" style="font-size:16px"></i>
            </button>
            <span class="breadcrumb-text">@yield('breadcrumb', 'Agenda Meeting')</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge role-badge" style="background:#e8f4ff;color:#1e2847">
                <i class="bi bi-person-badge me-1"></i>{{ $simUser['role'] }}
            </span>
            <div class="user-avatar" style="width:26px;height:26px;font-size:11px;background:{{ $simUser['color'] }}">
                {{ $simUser['abbrev'] }}
            </div>
            <span style="font-size:12.5px;font-weight:500;color:#374151">
                {{ $simUser['name'] }}
                <span style="color:#9ca3af;font-weight:400">– {{ $simUser['jabatan'] ?? $simUser['role'] }}</span>
            </span>
        </div>
    </header>

    <main class="content-wrap">
        @yield('content')
    </main>
</div>

{{-- PIC Conflict Modal (global, dipakai create/edit form) --}}
<div class="modal fade" id="picConflictModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#fef2f2;border-bottom:1px solid #fecaca">
                <h6 class="modal-title" style="color:#dc2626;font-size:13px">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konflik Jadwal PIC
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <div id="picConflictList" style="font-size:12.5px;color:#374151"></div>
                <div class="mt-3" style="font-size:11.5px;color:#dc2626;font-weight:600">
                    <i class="bi bi-lock-fill me-1"></i>Agenda tidak bisa disimpan. Ubah PIC atau jam meeting terlebih dahulu.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Toast notifications --}}
<div class="toast-container position-fixed top-0 start-50 translate-middle-x pt-3" style="z-index:9999">
    @if(session('success'))
    <div id="toastSuccess" class="toast align-items-center border-0 shadow"
         role="alert" style="background:#fff;min-width:300px;border-radius:10px">
        <div class="d-flex align-items-center gap-3 px-3 py-3">
            <div style="width:34px;height:34px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:17px"></i>
            </div>
            <div style="font-size:13px;color:#1f2937;font-weight:500">{{ session('success') }}</div>
        </div>
        <div style="height:3px;background:#16a34a;border-radius:0 0 10px 10px"></div>
    </div>
    @endif
    @if(session('error'))
    <div id="toastError" class="toast align-items-center border-0 shadow"
         role="alert" style="background:#fff;min-width:300px;border-radius:10px">
        <div class="d-flex align-items-center gap-3 px-3 py-3">
            <div style="width:34px;height:34px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;font-size:17px"></i>
            </div>
            <div style="font-size:13px;color:#1f2937;font-weight:500">{{ session('error') }}</div>
        </div>
        <div style="height:3px;background:#dc2626;border-radius:0 0 10px 10px"></div>
    </div>
    @endif
    @if(session('info'))
    <div id="toastInfo" class="toast align-items-center border-0 shadow"
         role="alert" style="background:#fff;min-width:300px;border-radius:10px">
        <div class="d-flex align-items-center gap-3 px-3 py-3">
            <div style="width:34px;height:34px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-info-circle-fill" style="color:#2563eb;font-size:17px"></i>
            </div>
            <div style="font-size:13px;color:#1f2937;font-weight:500">{{ session('info') }}</div>
        </div>
        <div style="height:3px;background:#2563eb;border-radius:0 0 10px 10px"></div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    ['toastSuccess', 'toastError', 'toastInfo'].forEach(function (id) {
        var el = document.getElementById(id)
        if (el) {
            new bootstrap.Toast(el, { delay: 3500, autohide: true }).show()
        }
    })
})

function picExtFormField(extDivisions, initDivisions, initPeople) {
    return {
        extDivisions,
        selectedDivisions: initDivisions || [],
        selectedPeople:    initPeople    || [],
        openDivDrop:    false,
        openPeopleDrop: false,
        get availablePeople() {
            const list = []
            for (const [code, div] of Object.entries(this.extDivisions)) {
                if (this.selectedDivisions.includes(code)) {
                    for (const [name, role] of Object.entries(div.members || {})) {
                        list.push({ name, role, div: code })
                    }
                }
            }
            return list
        },
        toggleDiv(code) {
            if (this.selectedDivisions.includes(code)) {
                this.selectedDivisions = this.selectedDivisions.filter(x => x !== code)
                const members = Object.keys(this.extDivisions[code]?.members || {})
                this.selectedPeople = this.selectedPeople.filter(p => !members.includes(p))
            } else {
                this.selectedDivisions.push(code)
            }
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

function conflictChecker(init) {
    return {
        tanggal:     init.tanggal     || '',
        jamMulai:    init.jamMulai    || '',
        jamSelesai:  init.jamSelesai  || '',
        ruanganId:   init.ruanganId   || '',
        picInternal: init.picInternal || '',
        excludeId:   init.excludeId   || null,
        conflicts:   null,
        picError:    false,
        picErrorMsg: '',
        _timer:      null,

        onTimeChanged(detail) {
            if (detail.name === 'jam_mulai')   { this.jamMulai  = detail.value; this.scheduleCheck() }
            if (detail.name === 'jam_selesai') { this.jamSelesai = detail.value; this.scheduleCheck() }
        },

        onPicChanged(detail) {
            this.picInternal = detail.value
            this.picError    = false
            this.scheduleCheck()
        },

        scheduleCheck() {
            clearTimeout(this._timer)
            this._timer = setTimeout(() => this.doCheck(), 450)
        },

        async doCheck() {
            if (!this.tanggal || !this.jamMulai || !this.jamSelesai) { this.conflicts = null; return }
            const params = new URLSearchParams({
                tanggal:     this.tanggal,
                jam_mulai:   this.jamMulai,
                jam_selesai: this.jamSelesai,
            })
            if (this.ruanganId)   params.set('ruangan_id',   this.ruanganId)
            if (this.picInternal) params.set('pic_internal', this.picInternal)
            if (this.excludeId)   params.set('exclude_id',   this.excludeId)
            try {
                const res    = await fetch('/agenda/check-conflict?' + params)
                this.conflicts = await res.json()
            } catch (_) { this.conflicts = null }
        },

        get hasConflicts() {
            return this.conflicts &&
                (this.conflicts.room_conflict || (this.conflicts.pic_conflicts || []).length > 0)
        },

        async handleSubmit(e) {
            e.preventDefault()
            // Re-check saat submit agar data terkini
            await this.doCheck()
            const c = this.conflicts

            if (c && (c.pic_conflicts || []).length > 0) {
                // Hard block — PIC conflict
                this.picError    = true
                this.picErrorMsg = c.pic_conflicts.map(p =>
                    `${p.name} sudah ada jadwal ${p.meeting_code} jam ${p.jam}`
                ).join('; ')
                const list = document.getElementById('picConflictList')
                if (list) {
                    list.innerHTML = c.pic_conflicts.map(p =>
                        `<div class="mb-2">
                            <i class="bi bi-person-fill-exclamation me-1 text-danger"></i>
                            <strong>${p.name}</strong> — sudah ada meeting
                            <span style="font-family:monospace;font-size:11px">${p.meeting_code}</span>
                            jam <strong>${p.jam}</strong>
                            <div style="font-size:11px;color:#6b7280;margin-top:1px">${p.kegiatan}</div>
                        </div>`
                    ).join('')
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('picConflictModal')).show()
                return
            }

            if (c && c.room_conflict) {
                // Soft — room conflict only: tanya konfirmasi
                const ok = confirm(
                    `Ruangan sudah dipakai oleh meeting ${c.room_conflict.meeting_code} pada jam ${c.room_conflict.jam}.\n\nLanjutkan tetap menyimpan?`
                )
                if (!ok) return
            }

            e.target.submit()
        }
    }
}
</script>

@stack('scripts')
</body>
</html>
