<?php

namespace App\Http\Controllers\Promociones;

use App\Http\Controllers\Bitacoras\BitacoraController;
use App\Http\Controllers\Usuarios\Roles\RoleController;
use App\Http\Requests\Bitacoras\BitacoraRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Promociones\PromocionesRequest ;
use App\Models\Promociones;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Type\Integer;

class PromocionController extends Controller
{
    public function index(){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 12);
        if(Auth::check()){
            if($role){
                $promociones = Promociones::all();
                return view('profile.Promociones.promociones.promociones', compact('promociones'));
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
                return view('profile.Promociones.promociones.createPromocion');
            }
            return redirect('dashboard');
        }
        return redirect('auth.login');
    }

    public function edit(Integer $promocion_id){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 12);
        if(Auth::check()){
            if($role){
                $promocion = Promociones::where('id', $promocion_id);
                return view('profile.Promociones.promociones.editPromocion', compact('promocion'));
            }
            return redirect('dashboard');
        }
        return redirect('auth.login');
    }

    public function store(PromocionesRequest $request){
        $promocion = Promociones::create($request->all());

        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Promociones',
            'user_id' => Auth::id(),
            'fecha_hora' => date('Y-m-d H:i:s'),
            'datos_anteriores' => null,
            'datos_nuevos' => $promocion->all(),
            'ip_address' => $request->ip(),
        ]);
        $bitacoraController = new BitacoraController();
        $bitacoraController->storeInsert($bitacoraRequest);

        return $this->index();
    }

    public function update(PromocionesRequest $request, Integer $id){

        $promocionUpdate = Promociones::find($id); 
        $anterioresDatos = $promocionUpdate->all();  
        $promocionUpdate->update($request->all());
        $promocionUpdate->save();

        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Promociones',
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

    public function destroy(int $promocion){
        $promocion= Promociones::find($promocion);   
        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Promociones',
            'user_id' => Auth::id(),
            'fecha_hora' => date('Y-m-d H:i:s'),
            'datos_anteriores' =>  $promocion->all(),
            'datos_nuevos' =>null,
            'ip_address' => request()->ip(),
        ]);
        $bitacoraController = new BitacoraController();
        $bitacoraController->storeDelete($bitacoraRequest);

        $promocion->delete();
        return $this->index();  
    }
}
