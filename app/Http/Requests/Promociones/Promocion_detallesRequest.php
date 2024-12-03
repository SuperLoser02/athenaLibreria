<?php

namespace App\Http\Requests\Promociones;

use Illuminate\Foundation\Http\FormRequest;

class Promocion_detallesRequest extends FormRequest
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
        return [
            'producto_codigos' => ['required', 'array'],
            'producto_codigos*' => ['string', 'distinct', 'exists:productos,codigo'],
            'cantidad' => ['nullable', 'numeric', 'min:0'],
            'nombre' =>  ['required', 'string', 'max:50'],
            'descripcion' => ['required', 'string', 'max:500'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_final' => ['nullable', 'date'],
            'porcentaje' => ['required', 'integer', 'min:0'],
            'promocione_id' => ['required', 'integer', 'exists:Promociones,id']
        ];
    }
}
