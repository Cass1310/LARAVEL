@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Lista de Medidores</h1>
    <div class="row">
        @foreach ($medidores as $medidor)
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Medidor ID: {{ $medidor->device_id }}</h5>
                    <p class="card-text"><strong>Departamento:</strong> {{ $medidor->departamento->numero }}</p>
                    <a href="{{ route('medidores.show', $medidor->id) }}" class="btn btn-primary btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
