@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Edificio: {{ $edificio->nombre }}</h1>
        </div>
        <div class="card-body">
            <p><strong>Dirección:</strong> {{ $edificio->direccion }}</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2>Departamentos del Edificio</h2>
        </div>
        <div class="card-body">
            @if ($edificio->departamentos->isEmpty())
                <p>No hay departamentos registrados en este edificio.</p>
            @else
                <ul class="list-group">
                    @foreach ($edificio->departamentos as $departamento)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Departamento {{ $departamento->numero }}</strong>
                                @if($departamento->usuarios->count())
                                    <p class="card-text"><strong>Residentes:</strong>
                                        {{ $departamento->usuarios->pluck('name')->implode(', ') }}
                                    </p>
                                @else
                                    <p class="card-text"><strong>Residentes:</strong> No asignado</p>
                                @endif
                            </div>
                            <a href="{{ route('departamentos.show', $departamento->id) }}" class="btn btn-sm btn-primary">Ver detalles</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('edificios.index') }}" class="btn btn-secondary">Volver a la lista</a>
        </div>
    </div>
</div>
@endsection
