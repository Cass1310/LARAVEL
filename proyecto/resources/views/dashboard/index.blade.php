@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-primary">📊 Dashboard General</h2>

    <div class="row">
        <!-- Alertas Activas -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">🚨 Alertas Activas</div>
                <div class="card-body">
                    @forelse($alertasActivas as $alerta)
                        <p class="mb-2">
                            <strong>{{ $alerta->tipo }}</strong> — {{ $alerta->mensaje }} <br>
                            <small class="text-muted">{{ $alerta->created_at->diffForHumans() }}</small>
                            @if ($alerta->estado === 'pendiente')
                            <form action="{{ route('alertas.resolver', $alerta->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Marcar como resuelta</button>
                            </form>
                            @endif
                        </p>
                    @empty
                        <p>No hay alertas activas.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Estado de los Medidores -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">💧 Estado de los Medidores</div>
                <div class="card-body">
                    @if ($medidores->isEmpty())
                        <p>No hay medidores registrados.</p>
                    @else
                        <ul class="list-group">
                            @foreach($medidores as $medidor)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Medidor #{{ $medidor->id }}
                                    <span>{{ $medidor->departamento->numero ?? 'Sin departamento asignado' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
