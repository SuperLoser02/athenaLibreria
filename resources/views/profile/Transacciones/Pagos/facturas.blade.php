
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
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-1/5 bg-white shadow-lg p-4">
            
        </aside>

        <!-- Page Content -->
        <main class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Facturas</h1>
                    <!-- Buscador -->
                    <div class="relative w-full max-w-sm ml-auto m-2">
                        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Buscar por fecha o ID de usuario..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition duration-300">
                    </div>
                </div>

                <!-- Tabla de Facturas -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase">NRO</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase">Fecha</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase">Vendedor</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase">Cliente_CI</th>
                                <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($facturas as $factura)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-4 text-gray-700">{{ $factura->nro }}</td>
                                    <td class="py-4 px-4 text-gray-700">{{ $factura->fecha }}</td>
                                    <td class="py-4 px-4 text-gray-700">{{ $factura->user->name }}</td>
                                    <td class="py-4 px-4 text-gray-700">{{ $factura->cliente?->ci ?? 'N/A' }}</td>
                                    <!--<td class="py-4 px-4 text-center">
                                        
                                        <a href="#" class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-md">
                                            Imprimir PDF
                                        </a>
                                    </td>-->
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>
        function filterTable() {
            const searchInput = document.getElementById("searchInput").value.toLowerCase();
            const table = document.getElementById("facturaTable");
            const rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                const fecha = rows[i].getElementsByTagName("td")[1].textContent.toLowerCase();
                const userId = rows[i].getElementsByTagName("td")[2].textContent.toLowerCase();
                
                if (fecha.includes(searchInput) || userId.includes(searchInput)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>