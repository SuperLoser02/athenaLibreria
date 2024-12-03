<?php

namespace App\Http\Requests\Transaccion;

use Illuminate\Foundation\Http\FormRequest;

class CompraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rule= [
            'fecha' => 'required|date',
            'proveedore_id' => 'required|int',
         ];
            // Validación dinámica para el array de productos
        if ($this->has('productos')) {
            foreach ($this->input('productos') as $codigo => $producto) {
                $rules["productos.$codigo.selected"] = ['sometimes', 'in:1'];
                $rules["productos.$codigo.cantidad"] = ['required_with:productos.' . $codigo . '.selected', 'integer', 'min:1'];
                $rules["productos.$codigo.precio"] = ['required_with:productos.' . $codigo . '.selected', 'numeric', 'min:0.01'];
            }
         }
     return $rules; 
    }
}
