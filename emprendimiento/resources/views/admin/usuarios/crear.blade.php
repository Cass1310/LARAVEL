<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Crear Usuario') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Nuevo Usuario</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.usuarios.guardar') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="password" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol *</label>
                                <select class="form-select" name="rol" id="rolSelect" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="propietario">Propietario</option>
                                    <option value="residente">Residente</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" name="direccion">
                            </div>

                            <!-- Campos específicos para residentes -->
                            <div id="residenteFields" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">Departamento *</label>
                                    <select class="form-select" name="id_departamento">
                                        <option value="">Seleccionar departamento</option>
                                        @foreach($edificios as $edificio)
                                            <optgroup label="{{ $edificio->nombre }}">
                                                @foreach($edificio->departamentos as $departamento)
                                                    <option value="{{ $departamento->id }}">
                                                        {{ $departamento->numero_departamento }} - Piso {{ $departamento->piso }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Inicio *</label>
                                    <input type="date" class="form-control" name="fecha_inicio">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" name="fecha_fin">
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Crear Usuario
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