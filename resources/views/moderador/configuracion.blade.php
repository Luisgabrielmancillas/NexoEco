@extends('layouts.moderator-panel')
@section('title', 'Configuración · Moderación NexoEco')
@section('moderator-content')
@php($iniciales = collect(explode(' ', trim($user->nombre_completo ?: $user->name)))->filter()->take(2)->map(fn($word)=>mb_substr($word,0,1))->implode(''))
<div class="mod-section-heading"><div><h2>Tu cuenta de moderador</h2><p>Actualiza tus datos personales y protege tu acceso.</p></div></div>
<div class="mod-settings-grid"><div>@include('profile.partials.photo-form')@include('profile.partials.update-profile-information-form')</div><div>@include('profile.partials.update-password-form')</div></div>
@endsection
