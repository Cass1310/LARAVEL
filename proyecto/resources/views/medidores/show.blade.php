@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Detalles del Medidor</h1>
        </div>
        <div class="card-body">
            <p><strong>Medidor ID:</strong> {{ $medidor->device_id }}</p>
            <p><strong>Departamento:</strong> {{ $medidor->departamento->numero }}</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2>Consumos</h2>
        </div>
        <div class="card-body">
            @if ($medidor->consumos->isEmpty())
                <p>No se han registrado consumos para este medidor.</p>
            @else
                <ul class="list-group">
                    @foreach ($medidor->consumos as $consumo)
                        <li class="list-group-item">
                            <strong>Fecha:</strong> {{ $consumo->fecha_hora }} |
                            <strong>Litros:</strong> {{ $consumo->litros }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2>Alertas</h2>
        </div>
        <div class="card-body">
            @if ($medidor->alertas->isEmpty())
                <p>No hay alertas asociadas a este medidor.</p>
            @else
                <ul class="list-group">
                    @foreach ($medidor->alertas as $alerta)
                        <li class="list-group-item">
                            <strong>{{ $alerta->tipo }}</strong> - {{ $alerta->mensaje }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('medidores.index') }}" class="btn btn-secondary">Volver a la lista</a>
        </div>
    </div>
</div>
@endsection
