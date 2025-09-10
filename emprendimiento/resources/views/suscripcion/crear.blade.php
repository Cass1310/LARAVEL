<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Activar Suscripción') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="row">
                <!-- Planes -->
                <div class="col-lg-8">
                    <h4 class="mb-4">Selecciona tu Plan</h4>
                    <div class="row">
                        <!-- Plan Mensual -->
                        <div class="col-md-6 mb-4">
                            <div class="card plan-card">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Mensual</h5>
                                    <h3 class="text-primary">Bs./ {{ number_format($precios['mensual'], 2) }}</h3>
                                    <p class="text-muted">por mes</p>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Facturación mensual</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Sin compromiso</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Cancelación anytime</li>
                                    </ul>
                                    <button class="btn btn-outline-primary w-100"
                                            onclick="seleccionarPlan('mensual')">
                                        Seleccionar Mensual
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Anual -->
                        <div class="col-md-6 mb-4">
                            <div class="card plan-card border-primary">
                                <div class="card-header bg-primary text-white text-center">
                                    <strong>¡AHORRA 20%!</strong>
                                </div>
                                <div class="card-body text-center">
                                    <h5 class="card-title">Anual</h5>
                                    <h3 class="text-primary">Bs./ {{ number_format($precios['anual'], 2) }}</h3>
                                    <p class="text-muted">
                                        <small>Equivale a Bs./ {{ number_format($precios['anual'] / 12, 2) }}/mes</small>
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Ahorro del 20%</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Pago anual único</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Más económico</li>
                                    </ul>
                                    <button class="btn btn-primary w-100"
                                            onclick="seleccionarPlan('anual')">
                                        Seleccionar Anual
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Pago -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Datos de Pago</h5>
                        </div>
                        <div class="card-body">
                            <form id="formPago" action="{{ route('suscripcion.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tipo" id="planSeleccionado">
                                
                                <div class="mb-3">
                                    <label class="form-label">Método de Pago *</label>
                                    <select class="form-select" name="metodo_pago" required>
                                        <option value="">Seleccionar</option>
                                        <option value="visa">Visa</option>
                                        <option value="mastercard">MasterCard</option>
                                        <option value="paypal">PayPal</option>
                                    </select>
                                </div>

                                <div id="datosTarjeta" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Número de Tarjeta *</label>
                                        <input type="text" class="form-control" name="numero_tarjeta" 
                                               placeholder="1234 5678 9012 3456" maxlength="19">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Fecha Vencimiento *</label>
                                            <input type="text" class="form-control" name="fecha_vencimiento" 
                                                   placeholder="MM/AA" maxlength="5">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CVV *</label>
                                            <input type="text" class="form-control" name="cvv" 
                                                   placeholder="123" maxlength="3">
                                        </div>
                                    </div>
                                </div>

                                <div id="resumenPago" style="display: none;" class="alert alert-info">
                                    <strong>Resumen:</strong>
                                    <div id="detallesPlan"></div>
                                </div>

                                <button type="submit" class="btn btn-success w-100" disabled id="btnPagar">
                                    <i class="bi bi-credit-card me-2"></i>Pagar Ahora
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const precios = @json($precios);

            function seleccionarPlan(tipo) {
                document.getElementById('planSeleccionado').value = tipo;
                document.getElementById('btnPagar').disabled = false;
                
                // Mostrar resumen
                const detallesPlan = tipo === 'anual' 
                    ? `Plan Anual - Bs./ ${precios.anual.toFixed(2)} (Ahorro 20%)`
                    : `Plan Mensual - Bs./ ${precios.mensual.toFixed(2)}/mes`;
                
                document.getElementById('detallesPlan').textContent = detallesPlan;
                document.getElementById('resumenPago').style.display = 'block';

                // Marcar plan seleccionado
                document.querySelectorAll('.plan-card').forEach(card => {
                    card.classList.remove('border-primary', 'border-2');
                });
                event.currentTarget.closest('.plan-card').classList.add('border-primary', 'border-2');
            }

            // Mostrar/ocultar datos de tarjeta
            document.querySelector('[name="metodo_pago"]').addEventListener('change', function() {
                const mostrarTarjeta = this.value === 'visa' || this.value === 'mastercard';
                document.getElementById('datosTarjeta').style.display = mostrarTarjeta ? 'block' : 'none';
            });

            // Formatear número de tarjeta
            document.querySelector('[name="numero_tarjeta"]')?.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{4})/g, '$1 ').trim();
                e.target.value = value.substring(0, 19);
            });
        </script>
    @endpush
</x-app-layout>