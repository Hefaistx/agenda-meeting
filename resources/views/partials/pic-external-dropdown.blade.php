{{--
    Props:
      $name          - input name (e.g. 'pic_external')
      $selected      - array of currently selected names
      $dispatchEvent - (optional) window event name to dispatch on change
--}}
@php $dispatchEvent = $dispatchEvent ?? null; @endphp

<div x-data="{
        open: false,
        selected: {{ json_encode(array_values($selected)) }},
        selectedDiv: '',
        divisions: {{ json_encode(\App\Models\Meeting::$externalDivisions) }},
        get members() {
            return this.selectedDiv && this.divisions[this.selectedDiv]
                ? this.divisions[this.selectedDiv].members
                : {}
        },
        toggle(name) {
            if (this.selected.includes(name)) {
                this.selected = this.selected.filter(x => x !== name)
            } else {
                this.selected.push(name)
            }
            @if($dispatchEvent)
            this.$dispatch('{{ $dispatchEvent }}', { value: this.selected.join(', ') })
            @endif
        }
     }"
     class="pic-dropdown">

    {{-- Trigger --}}
    <div class="pic-trigger" @click="open = !open">
        <template x-if="selected.length === 0">
            <span style="color:#9ca3af;font-size:12px">— Pilih PIC Eksternal —</span>
        </template>
        <template x-for="p in selected" :key="p">
            <span class="pic-chip" x-text="p"></span>
        </template>
        <i class="bi bi-chevron-down ms-auto" style="font-size:11px;color:#9ca3af;flex-shrink:0"></i>
    </div>

    {{-- Dropdown --}}
    <div class="pic-list" x-show="open" x-cloak @click.outside="open = false"
         style="min-width:240px;max-height:none">

        {{-- Division pills --}}
        <div style="padding:8px 10px;border-bottom:1px solid #eff0f1;background:#f8fafc">
            <div style="font-size:9.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px">
                Pilih Divisi
            </div>
            <div class="d-flex gap-1 flex-wrap">
                @foreach(\App\Models\Meeting::$externalDivisions as $code => $div)
                <button type="button"
                        @click.prevent="selectedDiv = (selectedDiv === '{{ $code }}') ? '' : '{{ $code }}'"
                        :style="selectedDiv === '{{ $code }}'
                            ? 'background:var(--teal);color:#fff;border-color:var(--teal)'
                            : 'background:#fff;color:#374151;border-color:#d1d5db'"
                        class="btn btn-sm"
                        style="font-size:10.5px;padding:2px 9px;border-radius:3px;border:1px solid #d1d5db">
                    {{ $code }}
                    <span style="font-size:9px;opacity:0.7">· {{ $div['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Member list --}}
        <template x-if="selectedDiv">
            <div>
                <template x-for="[name, role] in Object.entries(members)" :key="name">
                    <label style="display:flex;align-items:flex-start;gap:8px;padding:7px 12px;cursor:pointer;border-bottom:1px solid #f5f5f5;margin:0">
                        <input type="checkbox" class="form-check-input mt-1"
                               :value="name"
                               :checked="selected.includes(name)"
                               @change="toggle(name)">
                        <div>
                            <div x-text="name" style="font-size:12.5px;color:#1e2847;font-weight:500"></div>
                            <div x-text="role" style="font-size:10.5px;color:#9ca3af"></div>
                        </div>
                    </label>
                </template>
            </div>
        </template>
        <template x-if="!selectedDiv">
            <div style="padding:14px 12px;font-size:12px;color:#9ca3af;text-align:center;font-style:italic">
                Pilih divisi terlebih dahulu
            </div>
        </template>
    </div>

    <input type="hidden" name="{{ $name }}" :value="selected.join(', ')">
</div>
