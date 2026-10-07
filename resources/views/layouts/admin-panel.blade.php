@extends('layouts.base')
@section('site-header')
    @include('layouts.administrador.panel-header')
@endsection
@section('content')
<div class="nexo-container"><main class="admin-page">
    @if(session('success'))<div class="buyer-notice account-success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="buyer-notice" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="buyer-notice" role="alert">{{ $errors->first() }}</div>@endif
    @yield('admin-content')
</main></div>
@endsection
