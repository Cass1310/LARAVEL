@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Lista de Edificios</h1>
    <div class="row">
        @foreach ($edificios as $edificio)
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ $edificio->nombre }}</h5>
                    <p class="card-text">{{ $edificio->direccion }}</p>
                    <a href="{{ route('edificios.show', $edificio->id) }}" class="btn btn-primary btn-sm">Ver detalle</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
