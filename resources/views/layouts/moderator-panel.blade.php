@extends('layouts.base')
@section('body-class', 'moderation-workspace')
@section('site-header')@endsection
@section('content')
@php
    $moderatorName = auth()->user()->nombre_completo ?: auth()->user()->name;
    $initials = collect(explode(' ', trim($moderatorName)))->filter()->take(2)->map(fn($word) => mb_substr($word, 0, 1))->implode('');
    $modLinks = [
        ['moderador.dashboard', 'Resumen', 'grid', null],
        ['moderador.publicaciones', 'Publicaciones', 'file', 'publicaciones'],
        ['moderador.reportes', 'Reportes', 'flag', 'reportes'],
        ['moderador.vendedores', 'Vendedores', 'users', 'vendedores'],
        ['moderador.opiniones', 'Opiniones y comentarios', 'comment', 'opiniones'],
        ['moderador.analiticas', 'Analíticas', 'chart', null],
        ['moderador.configuracion', 'Configuración', 'settings', null],
        ['moderador.ayuda', 'Centro de ayuda', 'help', null],
    ];
@endphp
<aside class="mod-sidebar" data-mod-sidebar aria-label="Menú de moderación" id="moderator-sidebar">
    <a class="mod-brand" href="{{ route('moderador.dashboard') }}"><span class="mod-brand-symbol"><i></i><i></i><i></i></span>NexoEco</a>
    <button class="mod-sidebar-close" type="button" data-mod-close aria-label="Cerrar menú"><x-mod-icon name="close"/></button>
    <span class="mod-sidebar-label">Espacio de trabajo</span>
    <nav class="mod-main-nav" aria-label="Navegación del moderador">
    @foreach($modLinks as [$routeName, $label, $icon, $counter])
        <a href="{{ route($routeName) }}" class="{{ request()->routeIs($routeName) || ($routeName === 'moderador.vendedores' && request()->routeIs('moderador.solicitudes.*')) ? 'active' : '' }}" @if(request()->routeIs($routeName)) aria-current="page" @endif><x-mod-icon name="{{ $icon }}"/><span>{{ $label }}</span>@if($counter && ($counts[$counter] ?? 0))<b>{{ $counts[$counter] }}</b>@endif</a>
    @endforeach
    </nav>
    <form action="{{ route('logout') }}" method="POST" class="mod-logout">@csrf<button type="submit"><x-mod-icon name="logout"/>Cerrar sesión</button></form>
</aside>
<button class="mod-backdrop" data-mod-close type="button" aria-label="Cerrar menú" tabindex="-1"></button>
<div class="mod-workspace">
    <header class="mod-header"><button type="button" class="mod-menu-button" data-mod-menu aria-controls="moderator-sidebar" aria-expanded="false" aria-label="Abrir menú"><x-mod-icon name="menu"/></button><div class="mod-welcome"><span>Centro de moderación</span><h1>{{ request()->routeIs('moderador.dashboard') ? 'Hola, '.explode(' ', trim($moderatorName))[0] : ($pageTitle ?? 'Moderación') }}</h1><p>{{ request()->routeIs('moderador.dashboard') ? 'Aquí tienes lo que requiere tu atención hoy.' : 'Cuida el espacio de nuestra comunidad.' }}</p></div><div class="mod-header-actions"><form action="{{ route('moderador.publicaciones') }}" method="GET" class="mod-search"><x-mod-icon name="search"/><input type="search" name="q" maxlength="150" value="{{ request('q') }}" placeholder="Buscar publicación…" aria-label="Buscar publicación"><button type="submit" aria-label="Buscar"><kbd>↵</kbd></button></form><a class="mod-alert-button" href="{{ route('moderador.reportes') }}" aria-label="Reportes abiertos: {{ $counts['reportes'] ?? 0 }}"><x-mod-icon name="bell"/>@if($counts['reportes'] ?? 0)<b>{{ $counts['reportes'] }}</b>@endif</a><a class="mod-avatar" href="{{ route('moderador.configuracion') }}" aria-label="Mi cuenta">{{ mb_strtoupper($initials) }}</a></div></header>
    <main class="mod-page">
        @if(session('success'))<div class="mod-notice success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="mod-notice" role="alert">{{ $errors->first() }}</div>@endif
        @yield('moderator-content')
    </main>
</div>
@endsection
