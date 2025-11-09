<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Programar Nuevo Mantenimiento') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Datos del Mantenimiento</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('propietario.mantenimientos.guardar') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <!-- Selección de Edificio -->
                            <div class="col-md-6">
                                <label class="form-label">Edificio *</label>
                                <select class="form-select" id="select-edificio" name="id_edificio" required>
                                    <option value="">Seleccionar edificio</option>
                                    @foreach($edificios as $edificio)
                                        <option value="{{ $edificio->id }}">{{ $edificio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Selección de Departamento -->
                            <div class="col-md-6">
                                <label class="form-label">Departamento *</label>
                                <select class="form-select" id="select-departamento" name="id_departamento" required disabled>
                                    <option value="">Primero seleccione un edificio</option>
                                </select>
                            </div>

                            <!-- Selección de Medidor -->
                            <div class="col-md-6">
                                <label class="form-label">Medidor *</label>
                                <select class="form-select" id="select-medidor" name="id_medidor" required disabled>
                                    <option value="">Primero seleccione un departamento</option>
                                </select>
                                <small class="form-text text-muted">
                                    Solo se muestran medidores activos
                                </small>
                            </div>

                            <!-- Tipo de Mantenimiento -->
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Mantenimiento *</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="correctivo">Correctivo</option>
                                    <option value="preventivo">Preventivo</option>
                                    <option value="calibracion">Calibración</option>
                                </select>
                            </div>

                            <!-- Fecha -->
                            <div class="col-md-6">
                                <label class="form-label">Fecha del Mantenimiento *</label>
                                <input type="date" class="form-control" name="fecha" 
                                       value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label class="form-label">Descripción del Mantenimiento *</label>
                                <textarea class="form-control" name="descripcion" rows="4" 
                                          placeholder="Describa el mantenimiento a realizar..." required></textarea>
                                <small class="form-text text-muted">
                                    Máximo 200 caracteres
                                </small>
                            </div>

                            <!-- Botones -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Programar Mantenimiento
                                </button>
                                <a href="{{ route('propietario.mantenimientos') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const edificioSelect = document.getElementById('select-edificio');
            const departamentoSelect = document.getElementById('select-departamento');
            const medidorSelect = document.getElementById('select-medidor');

            // Cargar departamentos cuando se selecciona un edificio
            edificioSelect.addEventListener('change', function() {
                const edificioId = this.value;
                
                if (edificioId) {
                    departamentoSelect.disabled = false;
                    medidorSelect.disabled = true;
                    medidorSelect.innerHTML = '<option value="">Primero seleccione un departamento</option>';
                    
                    // Limpiar departamentos
                    departamentoSelect.innerHTML = '<option value="">Cargando departamentos...</option>';
                    
                    // Hacer petición para obtener departamentos
                    fetch(`/propietario/edificios/${edificioId}/departamentos`)
                        .then(response => response.json())
                        .then(data => {
                            departamentoSelect.innerHTML = '<option value="">Seleccionar departamento</option>';
                            data.forEach(depto => {
                                departamentoSelect.innerHTML += 
                                    `<option value="${depto.id}">Depto. ${depto.numero_departamento} - Piso ${depto.piso}</option>`;
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            departamentoSelect.innerHTML = '<option value="">Error al cargar departamentos</option>';
                        });
                } else {
                    departamentoSelect.disabled = true;
                    medidorSelect.disabled = true;
                    departamentoSelect.innerHTML = '<option value="">Primero seleccione un edificio</option>';
                    medidorSelect.innerHTML = '<option value="">Primero seleccione un departamento</option>';
                }
            });

            // Cargar medidores cuando se selecciona un departamento
            departamentoSelect.addEventListener('change', function() {
                const departamentoId = this.value;
                
                if (departamentoId) {
                    medidorSelect.disabled = false;
                    
                    // Limpiar medidores
                    medidorSelect.innerHTML = '<option value="">Cargando medidores...</option>';
                    
                    // Hacer petición para obtener medidores
                    fetch(`/propietario/departamentos/${departamentoId}/medidores`)
                        .then(response => response.json())
                        .then(data => {
                            medidorSelect.innerHTML = '<option value="">Seleccionar medidor</option>';
                            data.forEach(medidor => {
                                medidorSelect.innerHTML += 
                                    `<option value="${medidor.id}">${medidor.codigo_lorawan} - ${medidor.estado}</option>`;
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            medidorSelect.innerHTML = '<option value="">Error al cargar medidores</option>';
                        });
                } else {
                    medidorSelect.disabled = true;
                    medidorSelect.innerHTML = '<option value="">Primero seleccione un departamento</option>';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>