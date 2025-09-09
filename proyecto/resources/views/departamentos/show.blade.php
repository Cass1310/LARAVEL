@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Detalles del Departamento</h1>
        </div>
        <div class="card-body">
            <p><strong>Número:</strong> {{ $departamento->numero }}</p>
            <p><strong>Edificio:</strong> {{ $departamento->edificio->nombre }}</p>
            @if($departamento->usuarios->count())
                <p class="card-text"><strong>Residentes:</strong>
                    {{ $departamento->usuarios->pluck('name')->implode(', ') }}
                </p>
            @else
                <p class="card-text"><strong>Residentes:</strong> No asignado</p>
            @endif

        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2>Medidores Asociados</h2>
        </div>
        <div class="card-body">
            @if ($departamento->medidores->isEmpty())
                <p>No hay medidores asociados a este departamento.</p>
            @else
                <ul class="list-group">
                    @foreach ($departamento->medidores as $medidor)
                        <li class="list-group-item">ID del Medidor: {{ $medidor->device_id }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('departamentos.index') }}" class="btn btn-secondary">Volver a la lista</a>
        </div>
    </div>
</div>
@endsection
