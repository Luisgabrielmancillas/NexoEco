@extends('layouts.admin-panel')
@section('title', 'Editar tipo de cuenta | NexoEco')
@section('admin-content')
<div class="seller-heading"><h1>Editar tipo de cuenta</h1></div>
@include('tipos_usuario.partials.form', ['tipo' => $tiposUsuario])
@endsection
