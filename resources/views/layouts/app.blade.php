@php
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();

    $layout = 'layouts.comprador.app';

    if ($user) {
        if ($user->tieneTipo('Administrador')) {
            $layout = 'layouts.administrador.app';
        } elseif ($user->tieneTipo('Moderador')) {
            $layout = 'layouts.moderador.app';
        } elseif ($user->tieneTipo('Vendedor')) {
            $layout = 'layouts.vendedor.app';
        } elseif ($user->tieneTipo('Comprador')) {
            $layout = 'layouts.comprador.app';
        }
    }
@endphp

@extends($layout)

@section('header')
    {{ $header ?? '' }}
@endsection

@section('content')
    {{ $slot }}
@endsection