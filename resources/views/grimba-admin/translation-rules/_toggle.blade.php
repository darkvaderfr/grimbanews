@php
    $checked = (bool) ($current ?? false);
    $help = $help ?? null;
@endphp
<div class="form-check form-switch">
    {{-- Unchecked-checkbox HTML idiom: a hidden 0 ensures the POST
         always carries the key even when the box is off, so the
         save handler can persist the boolean as false. --}}
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" class="form-check-input" id="{{ $name }}_field"
           name="{{ $name }}" value="1" @checked($checked)>
    <label class="form-check-label" for="{{ $name }}_field">{{ $label }}</label>
    @if($help)
        <div class="form-text text-muted small">{{ $help }}</div>
    @endif
</div>
