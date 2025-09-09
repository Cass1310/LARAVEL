@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Usuarios</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Rol</th>
                <th>Email</th>
                <th>Departamentos Asignados</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->name }}</td>
                    <td>{{ $usuario->rol }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>
                        @if ($usuario->departamentos->isEmpty())
                            <em>No asignado</em>
                        @else
                            <ul>
                                @foreach ($usuario->departamentos as $departamento)
                                    <li>{{ $departamento->nombre }} (Edificio: {{ $departamento->edificio->nombre ?? 'N/A' }})</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
