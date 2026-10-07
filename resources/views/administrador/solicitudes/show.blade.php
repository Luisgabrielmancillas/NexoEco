@extends('layouts.admin-panel')
@section('title', 'Revisión de vendedor · NexoEco')
@section('admin-content')
@include('moderador.partials.application-review', ['routePrefix' => 'admin', 'returnRoute' => 'admin.solicitudes.index'])
@endsection