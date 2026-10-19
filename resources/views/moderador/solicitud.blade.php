@extends('layouts.moderator-panel')
@section('title', 'Documentos del vendedor · NexoEco')
@section('moderator-content')
@include('moderador.partials.application-review', ['routePrefix' => 'moderador', 'returnRoute' => 'moderador.vendedores'])
@endsection
