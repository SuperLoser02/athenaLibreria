<?php

namespace App\Http\Controllers\Transacciones\Compras;

use App\Http\Controllers\Bitacoras\BitacoraController;
use App\Models\Compra;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Usuarios\Roles\RoleController;
use App\Http\Requests\Bitacoras\BitacoraRequest;
use App\Http\Requests\Transaccion\CompraRequest;
use App\Models\Proveedor;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComprasController extends Controller
{
    public function index(){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 6);
        if(Auth::check()){
            if($role){
                $compras = Compra::orderBy('fecha', 'desc')->get();//->paginate(10); // Ordenar por fecha se puede hacer un paginate 
                return view('profile.Transacciones.Compras.compras', compact('compras'));
            }
            return redirect('dashboard');
        }
        return redirect('auth.login'); 
    }

    public function create(){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 6);
        if(Auth::check()){ 
            if($role){
                $proveedores=Proveedor::select('id','nombre')->get();
                $productosConStockBajo = Stock::whereColumn('cantidad', '<=', 'min_stock')
                                                ->join('productos', 'stocks.producto_codigo', '=', 'productos.codigo')
                                                ->pluck('productos.nombre', 'productos.codigo'); // Pluck para obtener ['codigo' => 'nombre']
                return view('profile.Transacciones.Compras.createCompras', compact('proveedores', 'productosConStockBajo'));
            }
            return redirect('dashboard');
        }
        return redirect('auth.login');       
    }
    
    public function store(CompraRequest $request){        
     // Creación de la compra
     $compra = Compra::create([
                       'monto_total' => 0, // El monto total inicial es 0, el trigger lo actualizará
                       'fecha' => $request->fecha,
                       'user_id' => Auth::id(), // Obtiene el id del usuario que está haciendo la compra
                       'proveedore_id' => $request->proveedore_id,
                       ]);
    // Procesar cada producto seleccionado
    foreach ($request->productos as $codigo => $detalle) {
      if (!empty($detalle['selected'])) {
        $cantidad = $detalle['cantidad'];
            
        // Verificar el stock del producto antes de insertarlo
        $stock = Stock::where('producto_codigo', $codigo)->first();
            if(!$stock){
             return redirect()->back()->withErrors([
                                        "productos.{$codigo}" => "No se encontró información de stock para el producto {$codigo}."
                                       ])->withInput();  
            }

            if (($cantidad + $stock->cantidad) > $stock->max_stock) {
             return redirect()->back()->withErrors([
                                         "productos.{$codigo}.cantidad" => "La cantidad para el producto {$codigo} no puede exceder el stock máximo de {$stock->max_stock}."
                                        ])->withInput();
            }

            // Insertar el producto en la compra
            $compra->productos()->syncWithoutDetaching([
                                  $codigo => [
                                   'cantidad' => $detalle['cantidad'],
                                   'precio_unitario' => $detalle['precio'],
                                  ]
                                ]);
         }
     }
     $bitacoraRequest = new BitacoraRequest([
        'tabla_afectada' => 'compras',
        'user_id' => Auth::id(),
        'fecha_hora' => date('Y-m-d H:i:s'),
        'datos_anteriores' => null,
        // Usa toArray() para obtener solo los atributos, 
        //ya que la anterior version adjuntaba todas las compras 
        //en lugar de la que se hizo
        'datos_nuevos' =>$compra->toArray(), 
        'ip_address' => $request->ip(),
       ]);
       $bitacoraController = new BitacoraController();
       $bitacoraController->storeInsert($bitacoraRequest);

      // Redirigir al índice de compras después de la operación
      return redirect()->route('compra.index');

     //esto es una idea para explicar ventas nos dirije al pdf del detalle de compras 
     //return $this->pdf($compra->nro);
   }

   public function show(int $nro){
      $role_id = Auth::user()->role_id;
      $role = RoleController::hasPrivilegio($role_id, 6);
      if(Auth::check()){   
       if($role){      
       $compra=Compra::find($nro);
       if(!$compra){
        return redirect()->back()->with('error', 'Compra no encontrada'); 
       } // vista por si se hace un show como el de productos
       return view('profile.transaccion.compra.show',compact('compra'));
      }
     return redirect('dashboard');
    }
    return redirect('auth.login'); 
   }

    public function edit(int $nro){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 6);
        if(Auth::check()){   
        if($role){          
         $compra=Compra::find($nro);
         if(!$compra){
           return redirect()->back()->with('error', 'Compra no encontrada'); 
         }
         $proveedores=Proveedor::select('id','nombre')->get();
         $productosConStockBajo =Stock::whereColumn('cantidad', '<=', 'min_stock')
                         ->join('productos', 'stocks.producto_codigo', '=', 'productos.codigo')
                         ->whereNotIn('productos.codigo', $compra->productos->pluck('codigo')->toArray()) // Excluir productos ya comprados
                         ->pluck('productos.nombre', 'productos.codigo'); // Pluck para obtener ['codigo' => 'nombre']                              
         $productosDeLaCompra = $compra->productos;  // Esto devolverá los productos asociados a esta compra 
         return view('profile.Transacciones.Compras.editCompras',compact('compra','proveedores','productosConStockBajo','productosDeLaCompra'));
        }
        return redirect('dashboard');//hequear lo que me dijo chat gpt
    }
    return redirect('auth.login'); 
   }

    public function update(CompraRequest $request,int $nro){
     $compra=Compra::find($nro);
     if(!$compra){
        return redirect()->back()->with('error', 'Compra no encontrada');   
     } 
     $datosAnteriores=$compra->toArray();

     // Actualizar los datos de la compra
     $compra->update([
        'fecha' => $request->fecha,
        'proveedore_id' => $request->proveedore_id,
        'user_id' => Auth::id(),  // Obtener el id del usuario que está haciendo la compra
    ]);

    //manejamos los productos eliminados o actualizados
    foreach ($request->productos as $codigo=>$detalle) {
      if(!empty($detalle['selected'])){  
       $compra->productos()->syncWithoutDetaching([
            $codigo=>[
                 'cantidad'=> $detalle['cantidad'],
                 'precio_unitario'=>$detalle['precio']
                ],
            ]);
    }else{
      $compra->productos()->detach($codigo); // Eliminar productos desmarcados  
    }
  }
  $montoActualizado = $compra->productos()->sum(DB::raw('cantidad * precio_unitario')); // Consulta directa a los productos

  $bitacoraRequest = new BitacoraRequest([
    'tabla_afectada' => 'compras',
    'user_id' => Auth::id(),
    'fecha_hora' => date('Y-m-d H:i:s'),
    'datos_anteriores' => $datosAnteriores,
    'datos_nuevos' =>  array_merge($compra->toArray(), ['monto_total' => $montoActualizado]), // Incluye el monto actualizado
    'ip_address' => $request->ip(),
  ]);

  $bitacoraController = new BitacoraController();
  $bitacoraController->storeUpdate($bitacoraRequest);

  return redirect()->route('compra.index')->with('success', 'Compra actualizada exitosamente');
 }

    public function destroy(int $nro){
     $compra=Compra::find($nro);
     if (!$compra) {
        return redirect()->route('compra.index')->withErrors(['error' => 'La compra no existe o ya fue eliminada.']);
    }
     $bitacoraRequest = new BitacoraRequest([
        'tabla_afectada' => 'compras',
        'user_id' => Auth::id(),
        'fecha_hora' => date('Y-m-d H:i:s'),
        'datos_anteriores' =>  $compra->toArray(),
        'datos_nuevos' =>null,
        'ip_address' => request()->ip(),
    ]);
    $bitacoraController = new BitacoraController();
    $bitacoraController->storeDelete($bitacoraRequest);

     $compra->productos()->detach();
     $compra->delete();
     return redirect()->route('compra.index')->with('success', 'compra eliminada exitosamente');   
    }

    //genera un pdf especifico
    public function pdf(int $nro){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 6);
        if(Auth::check()){
            if($role){
             $compra=Compra::find($nro);
             if (!$compra) {
                return redirect()->route('compra.index')->withErrors(['error' => 'La compra no existe o ya fue eliminada.']);
             }        
             $pdf = Pdf::loadView('profile.Transacciones.Compras.pdf',compact('compra'));
             //return $pdf->stream(); <- para mostrar en una vista aparte
            return $pdf->download("compra_{$nro}.pdf"); //<-para descargar el pdf
          }
         return redirect('dashboard');
       }
       return redirect('auth.login'); 
    }

    //genera un pdf de todo como reporte 
    public function PDFall(){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 6);
        if(Auth::check()){ 
        if($role){     
          $compra=Compra::all();
          $pdf = Pdf::loadView('profile.Transacciones.Compras.pdfCompleto',compact('compra'));
          return $pdf->stream();
        }
        return redirect('dashboard');
       }
       return redirect('auth.login');
    }    
}
