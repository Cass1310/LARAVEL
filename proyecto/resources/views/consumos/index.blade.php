<h1>Lista de Consumos de Agua</h1>

@foreach ($consumos as $consumo)
    <div>
        <h3>Medidor: {{ $consumo->medidor->device_id }}</h3>
        <p>Fecha: {{ $consumo->fecha_hora }} | Litros: {{ $consumo->litros }}</p>
        <a href="{{ route('consumos.show', $consumo->id) }}">Ver detalle</a>
    </div>
@endforeach
