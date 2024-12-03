<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; text-align: center; padding: 8px; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 20px; text-align: center; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Factura</h1>
        <h3>Athenea</h3>
        <p><strong>Fecha:</strong> {{ $factura->fecha}}</p>
        <p><strong>Número de Factura:</strong> {{ $factura->nro }}</p>
    </div>
    
    <div>
        <p><strong>Cliente:</strong> {{ $factura->cliente->nombre }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Cantidad</th>
                <th>Producto</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
                   </tbody>
    </table>
    
    <div style="text-align: right; margin-top: 10px;">
    </div>
    
    <div class="footer">
        <p>Gracias por su compra.</p>
        <p>En caso de intercambios, presente esta factura.</p>
    </div>
</body>
</html>
