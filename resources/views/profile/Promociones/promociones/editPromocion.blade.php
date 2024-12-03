<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Editar Promoción</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    @include('layouts.sidebar')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <aside class="w-72"></aside>

        <!-- Page Content -->
        <main class="flex-1 p-8">
            <div class="container mx-auto py-8">
                <h1 class="text-3xl font-bold mb-6 text-gray-700">Editar Promoción</h1>
                
                <form action="{{ route('promocion.update', $promocion->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Nombre -->
                    <div class="mb-4">
                        <label for="nombre" class="block text-gray-700 font-semibold">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $promocion->nombre) }}" maxlength="50" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-4">
                        <label for="descripcion" class="block text-gray-700 font-semibold">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4" maxlength="500" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Describe los detalles de la promoción..." required>{{ old('descripcion', $promocion->descripcion) }}</textarea>
                    </div>

                    <!-- Fecha de inicio -->
                    <div class="mb-4">
                        <label for="fecha_inicio" class="block text-gray-700 font-semibold">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $promocion->fecha_inicio) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>

                    <!-- Fecha final -->
                    <div class="mb-4">
                        <label for="fecha_final" class="block text-gray-700 font-semibold">Fecha Final (opcional)</label>
                        <input type="date" id="fecha_final" name="fecha_final" value="{{ old('fecha_final', $promocion->fecha_final) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <!-- Porcentaje -->
                    <div class="mb-4">
                        <label for="porcentaje" class="block text-gray-700 font-semibold">Porcentaje</label>
                        <input type="number" id="porcentaje" name="porcentaje" value="{{ old('porcentaje', $promocion->porcentaje) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" min="0" required>
                    </div>

                    <!-- Cantidad (opcional) -->
                    <div class="mb-4">
                        <label for="cantidad" class="block text-gray-700 font-semibold">Cantidad</label>
                        <input type="number" id="cantidad" name="cantidad" value="{{ old('cantidad', $promocion->cantidad) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" min="0">
                    </div>

                    <!-- Promoción ID -->
                    <div class="mb-4">
                        <label for="promocione_id" class="block text-gray-700 font-semibold">ID de Promoción</label>
                        <select id="promocione_id" name="promocione_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                            <option value="" disabled selected>Selecciona una promoción</option>
                            @foreach($promocionTipo as $promocionTipo)
                                <option value="{{ $promocionTipo->id }}" {{ $promocion->promocione_id == $promocionTipo->id ? 'selected' : '' }}>{{ $promocionTipo->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Producto Códigos -->
                    <div class="mb-4">
                        <label for="productos" class="block text-gray-700 font-semibold">Selecciona los Productos</label>
                        <select id="productos" name="producto_codigos[]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" multiple required>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->codigo }}" 
                                    {{ in_array($producto->codigo, $promocion->productos->pluck('codigo')->toArray()) ? 'selected' : '' }}>
                                    {{ $producto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botón de envío -->
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300">Actualizar Promoción</button>
                </form>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        $(document).ready(function() {
            $('#productos').select2({
                placeholder: "Selecciona los productos",
                allowClear: true
            });
        });
    </script>
</body>
</html>
