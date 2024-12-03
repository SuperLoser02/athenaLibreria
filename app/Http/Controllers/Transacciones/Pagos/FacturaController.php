<?php

namespace App\Http\Controllers\Transacciones\Pagos;

use App\Http\Controllers\Usuarios\Roles\RoleController;
use App\Http\Requests\Transaccion\FacturaRequest;
use App\Models\Factura;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller
{
    public function index(){  
        $role_id = Auth::user()->role_id;
        $role_privilegio = RoleController::hasPrivilegio($role_id,privilegio_id: 1);
        if(Auth::check()){
            if($role_privilegio){
                $facturas = Factura::orderBy('fecha', 'desc')->get();
                return view('profile.Transacciones.pagos.facturas', compact('facturas'));
            }
            return redirect('dashboard');
        }
        return view('auth.login');
    }

    // Falta el show
    public function generatePDF(int $nro)
    {
        $factura = Factura::find($nro); // Obtiene la factura con los datos relacionados
        $pdf = Pdf::loadView('profile.Transacciones.Pagos.pdf', compact('factura')); // Genera el PDF con la vista correspondiente
        // aqui se puede debatir si mostrar el pdf o directo descargar ?
        return $pdf->download('factura_' . $factura->nro . '.pdf'); // Descarga el PDF
    }

    public function store(FacturaRequest $request,int $nroVenta){
           $factura = Factura::create([
            'formato_pago' => $request->formato_pago,
            'fecha' => date('Y-m-d H:i:s'),
            'cliente_ci' => $request->cliente_ci,
            'user_id' => auth()->id(),
            'venta_nro' =>$nroVenta
        ]);
        return $factura->nro; //nos serivra para una vez terminamos de registrar haga el pdf  
    }


    //funcion para hacer reportes
}
