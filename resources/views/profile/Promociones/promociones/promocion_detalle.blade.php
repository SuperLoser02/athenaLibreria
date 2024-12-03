<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('layouts.sidebar')
</head>
<body class="font-sans antialiased bg-gray-100">

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar -->
        <aside class="w-72"></aside>

        <!-- Page Content -->
        <main class="flex-1 p-8">
            <div class="container mx-auto py-8">
                <h1 class="text-3xl font-bold mb-6 text-gray-700">Listado de promociones</h1>

                <!-- Search and Register Button -->
                <div class="flex items-center justify-between mb-6">
                    <a href="{{ route('promocion.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300">
                        Registrar Promocion
                    </a>

                    <a href="{{ route('compra.reporte') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-lg transition duration-300">
                        Generar Reportes
                    </a>

                    <!-- Search Input -->
                    <div class="relative w-full max-w-sm ml-auto">
                        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Buscar promocion..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition duration-300">
                    </div>
                </div>

                <!-- Tabs for Vigentes and Expiradas -->
                <div class="tabs mb-6">
                    <button class="tablink active" onclick="openTab(event, 'Vigentes')">Promociones Vigentes</button>
                    <button class="tablink" onclick="openTab(event, 'Expiradas')">Promociones Expiradas</button>
                </div>

                <!-- Contenido de las promociones vigentes -->
                <div id="Vigentes" class="tabcontent">
                    <div class="max-w-full overflow-auto shadow-lg border border-gray-300 rounded-lg" style="max-height: 400px;">
                        <table id="clienteTable" class="min-w-full bg-white rounded-lg">
                            <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium">Nombre</th>
                                    <th class="px-6 py-3 text-left font-medium">Fecha Inicio</th>
                                    <th class="px-6 py-3 text-left font-medium">Fecha Fin</th>
                                    <th class="px-6 py-3 text-left font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 text-sm font-light">
                                @foreach($promociones_vigentes as $promocion)
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promocion->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promocion->fecha_inicio }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promocion->fecha_final }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('promocion.show', $promocion->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded-lg transition duration-300">
                                                Ver promoción
                                            </a>
                                            <a href="{{ route('promocion.edit',$promocion->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-lg transition duration-300">
                                                Editar
                                            </a>
                                            <form action="{{ route('promocion.delete', $promocion->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta promoción?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg transition duration-300">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Contenido de las promociones expiradas -->
                <div id="Expiradas" class="tabcontent">
                    <div class="max-w-full overflow-auto shadow-lg border border-gray-300 rounded-lg" style="max-height: 400px;">
                        <table id="clienteTable" class="min-w-full bg-white rounded-lg">
                            <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium">Nombre</th>
                                    <th class="px-6 py-3 text-left font-medium">Fecha Inicio</th>
                                    <th class="px-6 py-3 text-left font-medium">Fecha Fin</th>
                                    <th class="px-6 py-3 text-left font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 text-sm font-light">
                                @foreach($promociones_expiradas as $promocion)
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promocion->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promocion->fecha_inicio }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promocion->fecha_final }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('promocion.show', $promocion->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded-lg transition duration-300">
                                                Ver promoción
                                            </a>
                                            <a href="{{ route('promocion.edit', $promocion->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-lg transition duration-300">
                                                Editar
                                            </a>
                                            <form action="{{ route('promocion.delete', $promocion->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta promoción?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg transition duration-300">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript for filtering the table -->
    <script>
        function filterTable() {
            const searchInput = document.getElementById("searchInput").value.toLowerCase();
            const tables = document.querySelectorAll('.tabcontent table');

            tables.forEach(function(table) {
                const rows = table.getElementsByTagName("tr");

                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName("td");
                    let match = false;

                    for (let j = 0; j < cells.length; j++) {
                        const cellContent = cells[j].textContent || cells[j].innerText;
                        if (cellContent.toLowerCase().includes(searchInput)) {
                            match = true;
                            break;
                        }
                    }

                    rows[i].style.display = match ? "" : "none";
                }
            });
        }

        // Tab functionality
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablink");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>
