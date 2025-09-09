@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Detalle de la Alerta</h1>
        </div>
        <div class="card-body">
            <p><strong>Medidor:</strong> {{ $alerta->medidor->device_id }}</p>
            <p><strong>Tipo:</strong> {{ $alerta->tipo }}</p>
            <p><strong>Mensaje:</strong> {{ $alerta->mensaje }}</p>
            <p><strong>Fecha:</strong> {{ $alerta->fecha_hora }}</p>
            <p><strong>Estado:</strong> {{ $alerta->estado }}</p>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('alertas.index') }}" class="btn btn-secondary">Volver a la lista</a>
        </div>
    </div>
</div>
@endsection
