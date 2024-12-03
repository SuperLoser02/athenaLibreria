<?php

namespace App\Http\Controllers\Transacciones\Ventas;

use App\Http\Controllers\Inventario\Productos\ProductoController;
use App\Http\Controllers\Usuarios\Roles\RoleController;
use App\Http\Requests\Transaccion\FacturaRequest;
use App\Http\Requests\Transaccion\VentaRequest;
use App\Models\Venta;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Promociones\Promociones\Promociones_detallesController;
use App\Http\Controllers\Transacciones\Pagos\FacturaController;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Stock;
use App\Models\Venta_Producto;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class VentasController extends Controller
{
    public function index(){
        $role_id = Auth::user()->role_id;
        $role_privilegio = RoleController::hasPrivilegio($role_id,privilegio_id: 1);
        if(Auth::check()){
            if($role_privilegio){
                $venta = Venta::orderBy('fecha', 'desc')->get();    
                return view('profile.Transacciones.ventas.ventas', compact('venta'));
            }
           return redirect('dashboard');
        }
        return view('auth.login');
    }

    public function create(){
        $role_id = Auth::user()->role_id;
        $role_privilegio = RoleController::hasPrivilegio($role_id,privilegio_id: 1);
        if(Auth::check()){
            if($role_privilegio){
                $productos = Producto::with('stocks')->get();
                $productosConPromocion = [];

    foreach ($productos as $producto) {
        // Verificar si el producto tiene una promoción vigente
        [$tienePromocion, $porcentajeDescuento] = (new Promociones_detallesController())->productoTienePromocionVigente($producto->codigo);
        
        $precioConDescuento = $producto->precio;
        // Calcular el precio con descuento si aplica
        if ($tienePromocion) {
            $descuento = ($producto->precio * $porcentajeDescuento) / 100;
            
            $precioConDescuento = $producto->precio - $descuento;
        }

        $productosConPromocion[] = [
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre,
            'precio' => $producto->precio,
            'stocks' => $producto->stocks->cantidad ?? 0,
            'tienePromocion' => $tienePromocion,
            'porcentajeDescuento' => $tienePromocion ? $porcentajeDescuento : null,
            'precioConDescuento' => $precioConDescuento,
            ];
                return view("profile.Transacciones.ventas.ventas",['productos' => $productosConPromocion]);
            }
        }
        return view('auth.login');
    }
 }

    public function store(VentaRequest $request){
        //creamos la venta primero
        
        $productos = $request->productos_array;
        $cantidades = $request->cantidad_array;
        $precioUnitarioTotal = $this->PrecioUnitarioYTotal($productos, $cantidades);
        $totalPagado = array_pop($precioUnitarioTotal);

        $venta = Venta::create([
            'monto_total' => $totalPagado,
        ]);

        $this->store_Venta_Producto($productos, $cantidades, $precioUnitarioTotal,$venta->nro);

        // creamos las facturas
        $requestFactura = new FacturaRequest([
            'cliente_ci'=> $request->cliente_ci,
            'formato_pago' => $request->formato_pago,
        ]);
         $facturaController=new FacturaController();
        $factura_nro =$facturaController->store($requestFactura,$venta->nro);
        
        if($request->cliete_ci != null){
            $cliente = Cliente::find($request->cliete_ci);
            $cliente->update(['putos'=> $cliente->puntos + $totalPagado*0.1]);
        }
        //return redirect()->route('venta.create')->with('status', 'venta creada exitosamente.');
         return $facturaController->generatePDF($factura_nro);
    }

    public function store_Venta_Producto(array $productos, array $cantidades, array $totalUnitario, $venta){
        
        foreach($productos as $index => $codigo){
            $cantidad = $cantidades[$index];
            $stock_producto = Stock::where('producto_codigo', $codigo)->first();
            $cantidad_actualizado = $stock_producto->cantidad - $cantidad;
            if($cantidad_actualizado >= 0){
                $total = $totalUnitario[$index];
                Venta_Producto::create([
                    'venta_nro'=> $venta,
                    'producto_codigo' => $codigo,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $total,
                ]);
            }
        }
        
        return ;
    }

    public function PrecioUnitarioYTotal(array $productos, array $cantidades){
        $precitoUnitarioTotal = array();
        $totalAPagar = 0;
        foreach($productos as $index => $producto){
            $cantidad = $cantidades[$index];
            $stock_producto = Stock::where('producto_codigo', $producto)->first();
            $cantidad_actualizado = $stock_producto->cantidad - $cantidad;
            if($cantidad_actualizado >= 0){
                $precio = ProductoController::precioDeUnProducto($producto);
                $precitoUnitarioTotal[] = $precio * $cantidad;
                $totalAPagar = $precio * $cantidad;

                $stock_producto->update([
                    'cantidad' => $cantidad_actualizado
                ]);
            }else{
                $precitoUnitarioTotal[] = 0;
            }
        }
        $precitoUnitarioTotal[] = $totalAPagar; 
        return $precitoUnitarioTotal;
    }
}
