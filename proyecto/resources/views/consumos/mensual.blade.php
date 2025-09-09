@extends('layouts.app')

@section('title', 'Consumo Mensual')

@section('content')
    <h2 class="mb-4">Consumo mensual de agua</h2>

    <form method="GET" action="{{ route('consumos.mensual') }}" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="edificio_id" class="col-form-label">Filtrar por edificio:</label>
            </div>
            <div class="col-auto">
                <label for="year" class="col-form-label">Año:</label>
            </div>
            <div class="col-auto">
                <select name="year" id="year" class="form-select">
                    @foreach ([2024, 2025] as $anio)
                        <option value="{{ $anio }}" {{ request('year', now()->year) == $anio ? 'selected' : '' }}>
                            {{ $anio }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <select name="edificio_id" id="edificio_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($edificios as $edificio)
                        <option value="{{ $edificio->id }}" {{ request('edificio_id') == $edificio->id ? 'selected' : '' }}>
                            {{ $edificio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <canvas id="consumoChart" width="400" height="150"></canvas>

    <script>
        const ctx = document.getElementById('consumoChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($meses) !!},
                datasets: [{
                    label: 'Litros consumidos',
                    data: {!! json_encode($litros) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Litros'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Mes'
                        }
                    }
                }
            }
        });
    </script>
@endsection
