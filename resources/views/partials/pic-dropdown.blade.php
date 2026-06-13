{{--
    Props:
      $name          - input name (e.g. 'pic_internal')
      $options       - array of PIC names (values)
      $selected      - array of currently selected names
      $labels        - (optional) assoc array name => display label e.g. "Nabil – Frontend Dev"
      $dispatchEvent - (optional) window event name to dispatch on change
--}}
@php
    $dispatchEvent = $dispatchEvent ?? null;
    $labels        = $labels ?? [];
@endphp

<div class="pic-dropdown"
     x-data="{
         open: false,
         selected: {{ json_encode(array_values($selected)) }},
         toggle(v) {
             if (this.selected.includes(v)) {
                 this.selected = this.selected.filter(x => x !== v)
             } else {
                 this.selected.push(v)
             }
             @if($dispatchEvent)
             this.$dispatch('{{ $dispatchEvent }}', { value: this.selected.join(', ') })
             @endif
         }
     }">

    {{-- Trigger --}}
    <div class="pic-trigger" @click="open = !open">
        <template x-if="selected.length === 0">
            <span style="color:#9ca3af;font-size:12px">— Pilih PIC —</span>
        </template>
        <template x-for="p in selected" :key="p">
            <span class="pic-chip" x-text="p"></span>
        </template>
        <i class="bi bi-chevron-down ms-auto" style="font-size:11px;color:#9ca3af;flex-shrink:0"></i>
    </div>

    {{-- Options list --}}
    <div class="pic-list" x-show="open" x-cloak @click.outside="open = false">
        @foreach($options as $opt)
        <label>
            <input type="checkbox"
                   class="form-check-input"
                   value="{{ $opt }}"
                   :checked="selected.includes('{{ $opt }}')"
                   @change="toggle('{{ $opt }}')">
            {{ $labels[$opt] ?? $opt }}
        </label>
        @endforeach
    </div>

    {{-- Hidden input for form submission --}}
    <input type="hidden" name="{{ $name }}" :value="selected.join(', ')">
</div>
