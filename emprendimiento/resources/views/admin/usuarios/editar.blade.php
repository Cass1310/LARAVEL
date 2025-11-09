<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Editar Usuario') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Editar Usuario: {{ $user->nombre }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.usuarios.actualizar', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" name="nombre" value="{{ $user->nombre }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="password">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" name="password_confirmation">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol *</label>
                                <select class="form-select" name="rol" id="rolSelect" required>
                                    <option value="propietario" {{ $user->rol == 'propietario' ? 'selected' : '' }}>Propietario</option>
                                    <option value="residente" {{ $user->rol == 'residente' ? 'selected' : '' }}>Residente</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono" value="{{ $user->telefono }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" name="direccion" value="{{ $user->direccion }}">
                            </div>

                            <!-- Campos específicos para residentes -->
                            <div id="residenteFields" style="display: {{ $user->rol == 'residente' ? 'block' : 'none' }};">
                                @php
                                    $departamentoActual = $user->departamentosResidente->first();
                                @endphp
                                <div class="col-md-6">
                                    <label class="form-label">Departamento *</label>
                                    <select class="form-select" name="id_departamento">
                                        <option value="">Seleccionar departamento</option>
                                        @foreach($edificios as $edificio)
                                            <optgroup label="{{ $edificio->nombre }}">
                                                @foreach($edificio->departamentos as $departamento)
                                                    <option value="{{ $departamento->id }}" 
                                                        {{ $departamentoActual && $departamentoActual->id == $departamento->id ? 'selected' : '' }}>
                                                        {{ $departamento->numero_departamento }} - Piso {{ $departamento->piso }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Inicio *</label>
                                    <input type="date" class="form-control" name="fecha_inicio" 
                                           value="{{ $departamentoActual ? $departamentoActual->pivot->fecha_inicio->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" name="fecha_fin" 
                                           value="{{ $departamentoActual && $departamentoActual->pivot->fecha_fin ? $departamentoActual->pivot->fecha_fin->format('Y-m-d') : '' }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Actualizar Usuario
                                </button>
                                <a href="{{ route('admin.usuarios') }}" class="btn btn-secondary">
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
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
            }

            document.getElementById('rolSelect').addEventListener('change', function() {
                const residenteFields = document.getElementById('residenteFields');
                if (this.value === 'residente') {
                    residenteFields.style.display = 'block';
                } else {
                    residenteFields.style.display = 'none';
                }
            });
        </script>
    @endpush
</x-app-layout>