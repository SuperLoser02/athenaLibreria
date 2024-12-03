<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts y Scripts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('layouts.sidebar')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-1/5"></aside>

        <!-- Page Content -->
        <main class="flex-1 p-8">
            <h1 class="text-2xl font-semibold mb-4">Intercambios</h1>

            <form method="POST" action="{{ route('intercambio.store') }}">
                @csrf

                <!-- Motivo -->
                <div class="mb-4">
                    <label for="motivo" class="block font-medium">Motivo del Intercambio:</label>
                    <textarea name="motivo" class="block w-full border-gray-300 rounded-md" maxlength="500" required placeholder="Escribe el motivo aquí..."></textarea>
                </div>

                <!-- Selección de productos y cantidades -->
                <div id="product-list" class="mb-4">
                    <div class="product-item mb-4 flex space-x-4">
                        <div>
                            <label for="producto_codigo[]" class="block font-medium">Producto:</label>
                            <select name="producto_codigo[]" class="block w-full border-gray-300 rounded-md" onchange="updatePrice(this)">
                                <option value="">Seleccione un producto</option>
                                @foreach ($productos as $producto)
                                   @if($producto->stocks->cantidad > 0) <!-- Solo mostrar productos con stock -->
                                    <option value="{{ $producto->codigo }}" data-precio="{{ $producto->precio }}">
                                        {{ $producto->nombre }} (Stock: {{ $producto->stocks->cantidad ?? 'No disponible' }})
                                    </option>
                                   @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="cantidad[]" class="block font-medium">Cantidad:</label>
                            <input type="number" name="cantidad[]" class="block w-full border-gray-300 rounded-md" min="1" value="1" oninput="updateTotal()">
                        </div>

                        <button type="button" onclick="removeProduct(this)" class="mt-6 bg-red-500 text-white px-4 py-2 rounded-lg">Eliminar</button>
                    </div>
                </div>

                <!-- Botón para añadir más productos -->
                <button type="button" onclick="addProduct()" class="bg-blue-500 text-white px-4 py-2 rounded-lg mt-4">Añadir otro producto</button>

                <!-- Campo para CI del Cliente -->
                <div class="mt-4">
                    <label for="cliente_ci" class="block font-medium">CI del Cliente:</label>
                    <input type="text" name="cliente_ci" class="block w-full border-gray-300 rounded-md">
                </div>

                <!-- Botón para guardar la venta -->
                <button type="submit" class="mt-6 bg-green-500 text-white px-6 py-2 rounded-lg">Registrar Intercambio</button>
            </form>
        </main>
    </div>

    <script>
        function addProduct() {
            const productItem = document.querySelector('.product-item').cloneNode(true);
            document.getElementById('product-list').appendChild(productItem);
            updateTotal();
        }

        function removeProduct(button) {
            button.closest('.product-item').remove();
            updateTotal();
        }

        function updatePrice(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const priceField = selectElement.closest('.product-item').querySelector('.precio');
            const price = selectedOption.getAttribute('data-precio');
            priceField.value = price ? `$${price}` : '';
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.product-item').forEach(item => {
                const selectElement = item.querySelector('select');
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-precio')) || 0;
                const quantity = parseInt(item.querySelector('input[name="cantidad_array[]"]').value) || 1;
                total += price * quantity;
            });
            document.getElementById('total').value = `$${total.toFixed(2)}`;
        }
    </script>
</body>
</html>
