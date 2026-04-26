@extends('plugins/member::themes.dashboard.layouts.master')

@section('header-meta')
    {{-- S168 — instant redirect to the Steve-styled /account landing
         for any visitor who hits the legacy /dashboard URL (post-
         login, post-register, post-password-reset, plus the "redirect
         logged-in members to dashboard" middleware all hardcode this
         route name on the Botble side). --}}
    <meta http-equiv="refresh" content="0; url={{ url('/account') }}">
@endsection

@section('content')
    <script>window.location.href = {!! json_encode(url('/account')) !!};</script>
    <noscript>
        <p>Redirection vers <a href="{{ url('/account') }}">Mon compte</a>…</p>
    </noscript>
@stop
