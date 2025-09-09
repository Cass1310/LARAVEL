@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Lista de Alertas</h1>
    <div class="row">
        @foreach ($alertas as $alerta)
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Medidor: {{ $alerta->medidor->device_id }}</h5>
                    <p class="card-text"><strong>{{ $alerta->tipo }}</strong> - {{ $alerta->mensaje }}</p>
                    <a href="{{ route('alertas.show', $alerta->id) }}" class="btn btn-primary btn-sm">Ver detalle</a>

                    @if ($alerta->estado === 'pendiente')
                    <form action="{{ route('alertas.resolver', $alerta->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Marcar como resuelta</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
