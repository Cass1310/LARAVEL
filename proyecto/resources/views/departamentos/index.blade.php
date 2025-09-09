@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Lista de Departamentos</h1>
    <div class="row">
        @foreach ($departamentos as $departamento)
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Departamento {{ $departamento->numero }}</h5>
                    <p class="card-text"><strong>Edificio:</strong> {{ $departamento->edificio->nombre }}</p>
                    @if($departamento->usuarios->count())
                        <p class="card-text"><strong>Residentes:</strong>
                            {{ $departamento->usuarios->pluck('name')->implode(', ') }}
                        </p>
                    @else
                        <p class="card-text"><strong>Residentes:</strong> No asignado</p>
                    @endif

                    <a href="{{ route('departamentos.show', $departamento->id) }}" class="btn btn-primary btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
