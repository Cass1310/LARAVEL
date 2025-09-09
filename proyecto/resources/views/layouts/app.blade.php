<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema de Consumo de Agua')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">LinkMeter Control</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="/dashboard" class="nav-link">Dashboard</a></li>
                    <li class="nav-item"><a href="/alertas" class="nav-link">Alertas</a></li>
                    <li class="nav-item"><a href="/edificios" class="nav-link">Edificios</a></li>
                    <li class="nav-item"><a href="/departamentos" class="nav-link">Departamentos</a></li>
                    <li class="nav-item"><a href="/medidores" class="nav-link">Medidores</a></li>
                    <li class="nav-item"><a href="/usuarios" class="nav-link">Usuarios</a></li>
                    <li class="nav-item"><a href="/consumo-mensual" class="nav-link">Consumo Mensual</a></li>
                    <li class="nav-item"><a href="{{ route('consumos.comparativa') }}" class="nav-link">Comparativa</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        @yield('content')
    </main>

    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p class="mb-0">© 2025 Sistema de Consumo de Agua. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
