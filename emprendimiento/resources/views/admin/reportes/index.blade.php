<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Reportes del Sistema') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Filtros de Reportes</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reportes') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-filter me-1"></i>Generar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen General -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <i class="bi bi-droplet fs-1"></i>
                            <h5 class="card-title mt-2">{{ number_format($reporteData['consumo']->total ?? 0, 2) }} m³</h5>
                            <p class="card-text">Consumo Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <i class="bi bi-currency-dollar fs-1"></i>
                            <h5 class="card-title mt-2">Bs./ {{ number_format($reporteData['facturacion']->total ?? 0, 2) }}</h5>
                            <p class="card-text">Facturación Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-danger text-white">
                        <div class="card-body">
                            <i class="bi bi-bell fs-1"></i>
                            <h5 class="card-title mt-2">{{ $reporteData['alertas']->sum('total') ?? 0 }}</h5>
                            <p class="card-text">Total Alertas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body">
                            <i class="bi bi-tools fs-1"></i>
                            <h5 class="card-title mt-2">{{ $reporteData['mantenimientos']->sum('total') ?? 0 }}</h5>
                            <p class="card-text">Mantenimientos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablas Detalladas -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Resumen de Consumo</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Total:</strong> {{ number_format($reporteData['consumo']->total ?? 0, 2) }} m³</p>
                            <p><strong>Promedio:</strong> {{ number_format($reporteData['consumo']->promedio ?? 0, 2) }} m³</p>
                            <p><strong>Registros:</strong> {{ $reporteData['consumo']->registros ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Resumen de Facturación</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Total:</strong> Bs./ {{ number_format($reporteData['facturacion']->total ?? 0, 2) }}</p>
                            <p><strong>Promedio:</strong> Bs./ {{ number_format($reporteData['facturacion']->promedio ?? 0, 2) }}</p>
                            <p><strong>Cantidad:</strong> {{ $reporteData['facturacion']->cantidad ?? 0 }} facturas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Exportación -->
            <div class="card">
                <div class="card-body text-center">
                    <button class="btn btn-outline-primary me-2" onclick="exportToPDF()">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Exportar a PDF
                    </button>
                    <button class="btn btn-outline-success me-2" onclick="exportToExcel()">
                        <i class="bi bi-file-earmark-excel me-1"></i>Exportar a Excel
                    </button>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Imprimir Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function exportToPDF() {
                alert('Exportando a PDF...');
            }

            function exportToExcel() {
                alert('Exportando a Excel...');
            }
        </script>
    @endpush
</x-app-layout>