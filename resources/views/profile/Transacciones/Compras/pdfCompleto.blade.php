<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Compras</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 20px auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 24px;
        }
        .header p {
            font-size: 16px;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Listado de Compras</h1>
        </div>

        @foreach ($compra as $compraItem)
        <div class="compra">
            <h2>Compra Nro: {{ $compraItem->nro }}</h2>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($compraItem->fecha)->format('d/m/Y') }}</p>
            <p><strong>Usuario:</strong> {{ $compraItem->users->name }}</p>
            <p><strong>Proveedor:</strong> {{ $compraItem->proveedore->nombre }}</p>

            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($compraItem->productos as $producto)
                        <tr>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->pivot->cantidad }}</td>
                            <td>{{ number_format($producto->pivot->precio_unitario, 2) }} Bs</td>
                            <td>{{ number_format($producto->pivot->cantidad * $producto->pivot->precio_unitario, 2) }} Bs</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total">
                <p><strong>Total de la Compra:</strong> {{ number_format($compraItem->monto_total, 2) }} Bs</p>
            </div>

            <!-- Page break after each purchase to avoid mixing the tables in one page -->
            <div class="page-break"></div>
        </div>
        @endforeach
    </div>
</body>
</html>