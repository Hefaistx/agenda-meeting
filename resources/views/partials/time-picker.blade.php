{{--
    Props:
      $name  - input name (e.g. 'jam')
      $value - current value HH:MM (e.g. '10:30')
--}}
@php
    $parts = explode(':', $value ?? '08:00');
    $initH = isset($parts[0]) && $parts[0] !== '' ? (int)$parts[0] : 8;
    $initM = isset($parts[1]) && $parts[1] !== '' ? (int)$parts[1] : 0;
    $uid   = 'tp_' . $name . '_' . uniqid();
@endphp

<div x-data="{
        open: false,
        h: {{ $initH }},
        m: {{ $initM }},
        pad(n) { return String(n).padStart(2,'0') },
        get display() { return this.pad(this.h) + ' : ' + this.pad(this.m) },
        get val()     { return this.pad(this.h) + ':' + this.pad(this.m) },
        pickH(v) {
            this.h = v;
            this.$nextTick(() => this.$dispatch('time-changed', { name: '{{ $name }}', value: this.val }))
        },
        pickM(v) {
            this.m = v;
            this.open = false;
            this.$nextTick(() => this.$dispatch('time-changed', { name: '{{ $name }}', value: this.val }))
        },
        close() {
            this.open = false;
            this.$nextTick(() => this.$dispatch('time-changed', { name: '{{ $name }}', value: this.val }))
        },
        scrollToCurrent() {
            this.$nextTick(() => {
                ['h','m'].forEach(part => {
                    const el = document.getElementById('{{ $uid }}_' + part + '_' + (part==='h' ? this.h : this.m))
                    if (el) el.scrollIntoView({ block: 'center' })
                })
            })
        }
     }"
     class="position-relative"
     style="display:block">

    {{-- Single display field --}}
    <div @click="open = !open; if(open) scrollToCurrent()"
         class="form-control d-flex align-items-center gap-2"
         style="cursor:pointer;width:100%;font-size:13.5px;font-weight:600;
                color:#1e2847;letter-spacing:0.5px;user-select:none">
        <i class="bi bi-clock" style="color:#9ca3af;font-size:13px"></i>
        <span x-text="display"></span>
    </div>

    {{-- Popover --}}
    <div x-show="open" x-cloak
         @click.outside="close()"
         class="position-absolute shadow"
         style="top:calc(100% + 4px);left:0;z-index:1050;background:#fff;
                border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;width:140px">

        {{-- Header --}}
        <div class="d-flex" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:6px 0">
            <div style="flex:1;text-align:center;font-size:10px;font-weight:700;color:#9ca3af;letter-spacing:1px">JAM</div>
            <div style="width:1px;background:#e2e8f0"></div>
            <div style="flex:1;text-align:center;font-size:10px;font-weight:700;color:#9ca3af;letter-spacing:1px">MENIT</div>
        </div>

        {{-- Columns --}}
        <div class="d-flex" style="height:180px">

            {{-- Jam --}}
            <div style="flex:1;overflow-y:auto;border-right:1px solid #e2e8f0" class="tp-col">
                @for($i = 0; $i <= 23; $i++)
                <div id="{{ $uid }}_h_{{ $i }}"
                     @click="pickH({{ $i }})"
                     :class="h === {{ $i }} ? 'tp-active' : 'tp-item'"
                     style="padding:7px 0;text-align:center;font-size:13px;font-weight:600;cursor:pointer">
                    {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                </div>
                @endfor
            </div>

            {{-- Menit --}}
            <div style="flex:1;overflow-y:auto" class="tp-col">
                @for($i = 0; $i <= 59; $i++)
                <div id="{{ $uid }}_m_{{ $i }}"
                     @click="pickM({{ $i }})"
                     :class="m === {{ $i }} ? 'tp-active' : 'tp-item'"
                     style="padding:7px 0;text-align:center;font-size:13px;font-weight:600;cursor:pointer">
                    {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                </div>
                @endfor
            </div>

        </div>
    </div>

    <input type="hidden" name="{{ $name }}" :value="val">
</div>

@once
<style>
.tp-item  { color: #374151; }
.tp-item:hover { background: #f0f4f8; }
.tp-active { background: #1e2847 !important; color: #fff !important; }
.tp-col::-webkit-scrollbar { width: 3px; }
.tp-col::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 2px; }
[x-cloak] { display: none !important; }
</style>
@endonce
