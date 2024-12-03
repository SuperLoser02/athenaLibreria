<!-- En la vista promocion_show.blade.php -->
<h1>Detalles de la Promoción: {{ $promocion->nombre }}</h1>
<p>fecha de inicio:{{ $promocion->fecha_inicio}} </p>
<p>fecha de final :{{ $promocion->fecha_final}} </p>
<p>descripcion :{{ $promocion->descripcion}} </p>
<h2>Productos Asociados:</h2>
<ul>
    @foreach($detalles as $producto)
    <div class="producto-detalle">
        <p>Nombre del producto: {{ $producto->nombre }}</p>
        <p>Cantidad: {{ $producto->pivot->cantidad }}</p>
        <p>Porcentaje: {{ $producto->pivot->porcentaje }}%</p>
    </div>
@endforeach
</ul>