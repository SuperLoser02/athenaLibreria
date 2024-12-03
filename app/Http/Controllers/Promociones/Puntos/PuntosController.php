<?php

namespace App\Http\Controllers\Promociones\Puntos;

use App\Http\Controllers\Bitacoras\BitacoraController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Usuarios\Roles\RoleController;
use App\Http\Requests\Bitacoras\BitacoraRequest;
use App\Http\Requests\Promociones\PuntosRequest;
use App\Models\Puntos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Type\Integer;

class PuntosController extends Controller
{
    public function index(){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 12);
        if(Auth::check()){
            if($role){
                $putos = Puntos::all();
                return view('profile.Promociones.Puntos.puntos', compact('putos'));
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
                return view('profile.Promociones.puntos.createPuntos');
            }
            return redirect('dashboard');
        }
        return redirect('auth.login');
    }

    public function edit(Integer $punto_id){
        $role_id = Auth::user()->role_id;
        $role = RoleController::hasPrivilegio($role_id, 12);
        if(Auth::check()){
            if($role){
                $promocion = Puntos::where('id', $punto_id);
                return view('profile.Promociones.puntos.editPuntos', compact('punto'));
            }
            return redirect('dashboard');
        }
        return redirect('auth.login');
    }

    public function store(PuntosRequest $request){
        $promocion = Puntos::create($request->all());

        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Puntos',
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

    public function update(PuntosRequest $request, Integer $id){

        $puntosUpdate = Puntos::find($id); 
        $anterioresDatos = $puntosUpdate->all();  
        $puntosUpdate->update($request->all());
        $puntosUpdate->save();

        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'puntos',
            'user_id' => Auth::id(),
            'fecha_hora' => date('Y-m-d H:i:s'),
            'datos_anteriores' => $anterioresDatos,
            'datos_nuevos' => $puntosUpdate->all(),
            'ip_address' => $request->ip(),
        ]);
        $bitacoraController = new BitacoraController();   
        $bitacoraController->storeUpdate($bitacoraRequest);

        return $this->index();
    }

    public function destroy(int $id){
        $puntos= Puntos::find($id);   
        $bitacoraRequest = new BitacoraRequest([
            'tabla_afectada' => 'Puntos',
            'user_id' => Auth::id(),
            'fecha_hora' => date('Y-m-d H:i:s'),
            'datos_anteriores' =>  $puntos->all(),
            'datos_nuevos' =>null,
            'ip_address' => request()->ip(),
        ]);
        $bitacoraController = new BitacoraController();
        $bitacoraController->storeDelete($bitacoraRequest);

        $puntos->delete();
        return $this->index();  
    }
}
