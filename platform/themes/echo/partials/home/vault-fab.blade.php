@php
    $ids = \App\Support\GrimbaVault::parseIds((string) request()->cookie(\App\Support\GrimbaVault::COOKIE, ''));
    $count = count($ids);
@endphp

@if(! request()->is('coffre*'))
    <a href="{{ url('/coffre') }}" class="grimba-vault-fab" data-grimba-vault-fab @if($count === 0) style="display:none;" @endif>
        <span aria-hidden="true">★</span>
        <span>Coffre</span>
        <strong data-grimba-vault-count>{{ $count }}</strong>
    </a>
@endif
