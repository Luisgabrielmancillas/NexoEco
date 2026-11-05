@extends('layouts.admin-panel')
@section('title', 'Crear tipo de cuenta | NexoEco')
@section('admin-content')
<div class="seller-heading"><h1>Crear tipo de cuenta</h1></div>
@include('tipos_usuario.partials.form', ['tipo' => null])
@endsection
