@extends('layouts.app')

@section('title', 'Comparativa de Consumo')

@section('content')
    <h2 class="mb-4">Comparativa de consumo de agua</h2>

    <form method="GET" action="{{ route('consumos.comparativa') }}" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="tipo" class="col-form-label">Tipo de comparación:</label>
            </div>
            <div class="col-auto">
                <select name="tipo" id="tipo" class="form-select">
                    <option value="trimestral" {{ request('tipo') == 'trimestral' ? 'selected' : '' }}>Trimestral</option>
                    <option value="semestral" {{ request('tipo') == 'semestral' ? 'selected' : '' }}>Semestral</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Comparar</button>
            </div>
        </div>
    </form>

    <canvas id="comparativaChart" width="400" height="150"></canvas>

    <script>
        const ctx = document.getElementById('comparativaChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($etiquetas) !!},
                datasets: [{
                    label: 'Litros consumidos',
                    data: {!! json_encode($valores) !!},
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Litros' }
                    },
                    x: {
                        title: { display: true, text: 'Periodo' }
                    }
                }
            }
        });
    </script>
@endsection
