<?php

namespace App\Http\Controllers\Promociones\Promociones;

use App\Http\Controllers\Bitacoras\BitacoraController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Usuarios\Roles\RoleController;
use App\Http\Requests\Bitacoras\BitacoraRequest;
use App\Http\Requests\Promociones\Promocion_detallesRequest;
use App\Models\Producto;
use App\Models\Promocion_detalles;
use App\Models\Promociones;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelLang\Publisher\Console\Update;
use Ramsey\Uuid\Type\Integer;

class Promociones_detallesController extends Controller
{
    public function index(){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 12);
        if(Auth::check()){
            if($role){
                $promociones_vigentes = $this->getPromocionesVigentes();
                $promociones_expiradas = $this->promocionesExpiradas();
                return view('profile.Promociones.promociones.promocion_detalle', compact('promociones_vigentes', 'promociones_expiradas'));
            }
            return redirect('dashboard');
        }
        return redirect('auth.login');
    }

    public function show(int $id){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 12);
        if(Auth::check()){
         if($role){
          $promocion = Promocion_detalles::find($id);
          if(!$promocion){
            return redirect()->back()->with('error', 'promocion no encontrada'); 
           } // vista por si se hace un show como el de productos
            //ahora existe una relacion entre procmocion detalle con producto
            $detalles = $promocion->productos;  // Aquí asumiendo que tienes una relación 'productos'
            return view('profile.Promociones.promociones.promocion_show',compact('promocion','detalles'));
          }
         return redirect('dashboard');
        }
        return redirect('auth.login'); 
    }

    public function create(){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 12);
        if(Auth::check()){
            if($role){
                $promocionTipo=Promociones::all();
                $productos = Producto::all();
                return view('profile.Promociones.promociones.createPromocion_detalle',compact('promocionTipo','productos'));
            }
            return redirect('dashboard');
        }
        return redirect('auth.login');
    }

    public function edit(int $promocion_detalle_id){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 12);
        if(Auth::check()){
            if($role){
                $promocion = Promocion_detalles::find($promocion_detalle_id);
                if(!$promocion){
                 return redirect()->back()->with('error', 'Promocion no encontrada');   
                }
                $promocionTipo=Promociones::all();
                $productos=Producto::all();
                 return view('profile.Promociones.promociones.editPromocion', compact('promocion','promocionTipo','productos'));
             }
            return redirect('dashboard');
        }
        return redirect('auth.login');
    }

    public function store(Promocion_detallesRequest $request){
        $promocion_detalle = Promocion_detalles::create($request->all());
        $this->asignarProductosAPromocionesInsert($request, $promocion_detalle->id);

        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Promocion detalle',
            'user_id' => Auth::id(),
            'fecha_hora' => date('Y-m-d H:i:s'),
            'datos_anteriores' => null,
            'datos_nuevos' => $promocion_detalle->all(),
            'ip_address' => $request->ip(),
        ]);
        $bitacoraController = new BitacoraController();
        $bitacoraController->storeInsert($bitacoraRequest);
      return $this->index();  
    }

    public function update(Promocion_detallesRequest $request, int $id){

        $promocionUpdate = Promocion_detalles::find($id); 
        $anterioresDatos = $promocionUpdate->all();  
        $promocionUpdate->update($request->only([
            'nombre',
            'descrtipcion',
            'fecha_inicio',
            'fecha_final',
            'promocione_id',
        ]));
        $promocionUpdate->save();
        $this->asignarProductosAPromocionesUpdate($request, $id);

        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Promocion detalle',
            'user_id' => Auth::id(),
            'fecha_hora' => date('Y-m-d H:i:s'),
            'datos_anteriores' => $anterioresDatos,
            'datos_nuevos' => $promocionUpdate->all(),
            'ip_address' => $request->ip(),
        ]);
        $bitacoraController = new BitacoraController();   
        $bitacoraController->storeUpdate($bitacoraRequest);

        return $this->index();
    }
    
    public function destroy(int $promocion_detalle){
        $promocion= Promocion_detalles::find($promocion_detalle);   
        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Promocion detalle',
            'user_id' => Auth::id(),
            'fecha_hora' => date('Y-m-d H:i:s'),
            'datos_anteriores' =>  $promocion->all(),
            'datos_nuevos' =>null,
            'ip_address' => request()->ip(),
        ]);
        $bitacoraController = new BitacoraController();
        $bitacoraController->storeDelete($bitacoraRequest);
        $this->asignarProductosAPromocionesDelete($promocion_detalle);
        $promocion->delete();
        return $this->index();  
    }

    //! ver despues

    public function getPromocionesVigentes()
    {
        $fechaActual = Carbon::now()->startOfDay(); // Fecha actual sin horas
    
        $promocionesVigentes = Promocion_detalles::with(['productos' => function ($query) {
            $query->select('codigo', 'nombre')
                  ->where(function ($query) {
                      $query->where('cantidad', '>', 0)
                            ->orWhereNull('cantidad');
                  });
        }])
        ->where('fecha_inicio', '<=', $fechaActual) // La promoción ya comenzó
        ->where(function ($query) use ($fechaActual) {
            $query->whereNull('fecha_final')
                  ->orWhere('fecha_final', '>=', $fechaActual); // La promoción no ha terminado
        })
        ->get();
    
        return $promocionesVigentes;
    }
    

    public function promocionesExpiradas(){
     $fechaActual = Carbon::now()->startOfDay();
     $promocionesExpiradas = DB::table('promocion_detalles as pd')
                                ->join('promociones as p', 'pd.promocione_id', '=', 'p.id')
                                ->where('pd.fecha_final', '<', $fechaActual) // Fecha de fin ya pasó
                                ->select('pd.*')
                                  ->get();
     return $promocionesExpiradas;
    }


    public function asignarProductosAPromocionesInsert( Promocion_detallesRequest $request,int $promocion_detalle_id){
        $productos_codigos = $request->producto_codigos;
        $promocion_detalle = Promocion_detalles::find($promocion_detalle_id);

        foreach($productos_codigos as $producto_codigo){
            $promocion_detalle->productos()->attach(
                $producto_codigo, 
                ['porcentaje' => $request->porcentaje, 'cantidad' => $request->cantidad]
            );
            $promocion_detalle_producto=[
                'promocion_detalle_id' => $promocion_detalle_id,
                'producto_codigo' => $producto_codigo,
                'porcentaje' => $request->porcentaje,
                'cantidad' => $request->cantidad,
            ];
            $bitacoraRequest = new BitacoraRequest([
                'tabla_afectada' => 'Promocion_detalle_producto',
                'user_id' => Auth::id(),
                'fecha_hora' => date('Y-m-d H:i:s'),
                'datos_anteriores' => null,
                'datos_nuevos' => $promocion_detalle_producto,
                'ip_address' => request()->ip(),
            ]);
            $bitacoraController = new BitacoraController();
            $bitacoraController->storeInsert($bitacoraRequest);
        }
    }

    public function asignarProductosAPromocionesUpdate($request, $promocion_detalle_id){
        $productos_codigos = $request->producto_codigos;
        $promocion_detalle = Promocion_detalles::find($promocion_detalle_id);

        foreach($productos_codigos as $producto_codigo){
            // Verificar si el producto ya está asociado a la promoción
            $productoExistente = $promocion_detalle->productos()->where('producto_codigo', $producto_codigo)->first();

            if ($productoExistente) {
            // Capturar los datos antiguos antes de la actualización
                $datosAntiguos = [
                    'promocion_detalle_id' => $promocion_detalle_id,
                    'producto_codigo' => $producto_codigo,
                    'porcentaje' => $productoExistente->pivot->porcentaje,
                    'cantidad' => $productoExistente->pivot->cantidad,
                ];

                // Actualizar los datos en la tabla pivote
                $promocion_detalle->productos()->updateExistingPivot(
                    $producto_codigo, 
                    ['porcentaje' => $request->porcentaje, 'cantidad' => $request->cantidad]
               );

                // Capturar los datos nuevos
                $datosNuevos = [
                    'promocion_detalle_id' => $promocion_detalle_id,
                    'producto_codigo' => $producto_codigo,
                    'porcentaje' => $request->porcentaje,
                    'cantidad' => $request->cantidad,
                ];

                $bitacoraRequest = new BitacoraRequest([
                    'tabla_afectada' => 'Promocion_detalle_producto',
                    'user_id' => Auth::id(),
                    'fecha_hora' => date('Y-m-d H:i:s'),
                    'datos_anteriores' => $datosAntiguos,
                    'datos_nuevos' => $promocion_detalle->all(),
                    'ip_address' => request()->ip(),
                ]);
                $bitacoraController = new BitacoraController();
                $bitacoraController->storeUpdate($bitacoraRequest);
            }else {
                // Si el producto no está asociado, puedes manejarlo aquí, si es necesario.
                // Por ejemplo, agregar el producto y luego registrar la bitácora.
                $promocion_detalle->productos()->attach(
                    $producto_codigo, 
                    ['porcentaje' => $request->porcentaje, 'cantidad' => $request->cantidad]
                );
            }
        }
    }

    public function asignarProductosAPromocionesDelete(int $promocion_detalle_id){
        $promocion_detalle = Promocion_detalles::find($promocion_detalle_id);
    
        // Verificar si existen productos asociados a la promoción
        if ($promocion_detalle) {
            $promocion_detalle_productos = $promocion_detalle->productos;
    
            foreach ($promocion_detalle_productos as $producto) {
                // Capturar los datos antiguos antes de la eliminación
                $datosAntiguos = [
                    'promocion_detalle_id' => $promocion_detalle_id,
                    'producto_codigo' => $producto->producto_codigo,
                    'porcentaje' => $producto->pivot->porcentaje,
                    'cantidad' => $producto->pivot->cantidad,
                ];

                 // Eliminar el producto de la relación muchos a muchos (tabla pivote)
                $promocion_detalle->productos()->detach($producto->producto_codigo);
    
                $bitacoraRequest = new BitacoraRequest([
                    'tabla_afectada' => 'Promocion_detalle_producto',
                    'user_id' => Auth::id(),
                    'fecha_hora' => date('Y-m-d H:i:s'),
                    'datos_anteriores' => $datosAntiguos,
                    'datos_nuevos' => null,
                    'ip_address' => request()->ip(),
                ]);

                $bitacoraController = new BitacoraController();
                $bitacoraController->storeDelete($bitacoraRequest);
            }
        }
    }

    public Function productoTienePromocionVigente(string $productoCodigo){
        $promociones = $this->getPromocionesVigentes();
        //dd($promociones);
        foreach($promociones as $promocion){
                if($promocion->id != 1){
                    $promocion_detalle_producto =$promocion->productos()
                    ->where('producto_codigo', $productoCodigo)
                    ->first() // Usar first() para obtener solo el primer resultado
                    ->pivot;
                     // Si el producto está asociado a la promoción, devolver el porcentaje
                    if($promocion_detalle_producto){
                        return [true,$promocion_detalle_producto->porcentaje];
                    }
                }
            }
        return [false,null];
    }

    public static function Aumentar(string $producto_codigo, Integer $cantidad){
       // Obtener la promoción vigente para el producto
       $promocion = Promocion_detalles::whereHas('productos', function($query) use ($producto_codigo) {
         $query->where('producto_codigo', $producto_codigo);
        })->where('promocione_id', 1)->first();

        if (!$promocion) {
            throw new \Exception("Promoción no encontrada para el producto con código: $producto_codigo.");
        }
        // Verificar si el producto está en la relación de productos de la promoción
       $promocion_detalle_producto = $promocion->productos()->where('producto_codigo', $producto_codigo)->first();

       if (!$promocion_detalle_producto) {
         throw new \Exception("El producto con código $producto_codigo no está asociado a la promoción.");
       }

        // Aumentar la cantidad del producto en la relación
        $promocion_detalle_producto->pivot->cantidad += $cantidad;

        // Guardar los cambios en la tabla pivote
        $promocion_detalle_producto->pivot->save();

        return $promocion_detalle_producto;
    }
}