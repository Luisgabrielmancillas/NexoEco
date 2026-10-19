@extends('layouts.admin-panel')
@section('title', 'Crear cuenta · NexoEco')
@section('admin-content')
<div class="admin-heading"><span class="buyer-eyebrow">Administración · Usuarios</span><h1>Crear cuenta rápida</h1><p class="buyer-description">Da de alta un comprador, moderador o administrador en un solo paso.</p></div>
<section class="buyer-panel">@include('administrador.usuarios.partials.create-form')</section>
<a class="buyer-text-link" href="{{ route('admin.users.index') }}">Volver a usuarios</a>
@endsection
