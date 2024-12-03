<?php

namespace App\Http\Controllers\Promociones\Intercambio;

use App\Http\Controllers\Bitacoras\BitacoraController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Promociones\Promociones_detallesController;
use App\Http\Controllers\Usuarios\Roles\RoleController;
use App\Http\Requests\Bitacoras\BitacoraRequest;
use App\Http\Requests\Transaccion\IntercambioRequest;
use App\Models\Intercambios;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Integer;

class IntercambioController extends Controller
{

  public function index(){
    $role_id = Auth::user()->role_id;
    $role = RoleController::hasPrivilegio($role_id, 13);
    if(Auth::check()){
        if($role){
            $intercambios=Intercambios::orderBy('fecha', 'desc')->get();
            return view('profile.Transacciones.Intercambios.Intercambio',compact('intercambios'));
        }
        return redirect('dashboard');
    }
    return redirect('auth.login');
  }

    public function create(){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 13);
        if(Auth::check()){
            if($role){
                $productos=Producto::all();
                return view('profile.Transacciones.Intercambios.createIntercambio',compact('productos'));
            }
            return redirect('dashboard');
        }
        return redirect('auth.login');
    }

    public function store(IntercambioRequest $request){
        $intercambio = Intercambios::create([
            'fecha' =>  date('Y-m-d H:i:s'),
            'motivo' => $request->motivo,
            'user_id' =>Auth::id(),
            'cliente_ci' => $request->cliente_ci
        ]);
        $productos_codigos = $request->producto_codigo;
        $cantidades = $request->cantidad;
        $this->asignarIntercambioAProductos($productos_codigos, $intercambio, $cantidades);

        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Intercambio',
            'user_id' => Auth::id(),
            'fecha_hora' => date('Y-m-d H:i:s'),
            'datos_anteriores' => null,
            'datos_nuevos' => $intercambio->all(),
            'ip_address' => $request->ip(),
        ]);
        $bitacoraController = new BitacoraController();
        $bitacoraController->storeInsert($bitacoraRequest);

        return $this->index();
    }


    public function asignarIntercambioAProductos(array $productos_codigo, Intercambios $intercambio, array $cantidades){
        foreach($productos_codigo as $index => $producto_codigo){
            $cantidad = $cantidades[$index];
            $intercambio->productos()->attach($producto_codigo, ['cantidad' => $cantidad]);

            $intercambio_producto = $intercambio->productos()->where('producto_codigo', $producto_codigo)->first();

            // Obtener el producto relacionado con el código de producto
        $producto = Producto::where('codigo', $producto_codigo)->first();
        
        // Obtener el stock del producto
        $stock = $producto->stocks;

        if ($stock && $stock->cantidad >= $cantidad) {
            // Descontar la cantidad del inventario
            $stock->cantidad -= $cantidad;
            $stock->cantidad_defectuosa += $cantidad;
            $stock->save();
        }else{
          // Si no hay suficiente stock, lanzar un error o manejar la situación
          // Dependiendo de la lógica de negocio que necesites, podrías lanzar una excepción
         throw new \Exception("No hay suficiente stock para el producto {$producto_codigo}");
        }

            $bitacoraRequest = new BitacoraRequest([
                'tabla_afectada' => 'Intercambio Producto',
                'user_id' => Auth::id(),
                'fecha_hora' => date('Y-m-d H:i:s'),
                'datos_anteriores' => null,
                'datos_nuevos' => $intercambio_producto->all(),
                'ip_address' => request()->ip(),
            ]);
            $bitacoraController = new BitacoraController();
            $bitacoraController->storeInsert($bitacoraRequest);
            
 //           Promociones_detallesController::Aumentar($producto_codigo, $cantidad);
        }
    }

}