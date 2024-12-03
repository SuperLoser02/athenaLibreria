<?php

namespace App\Http\Requests\Transaccion;

use Illuminate\Foundation\Http\FormRequest;

class IntercambioRequest extends FormRequest
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
            'cantidad' => ['required', 'array'],
            'cantidad*' => ['numeric'],
            'producto_codigo' => ['required', 'array'],
            'producto_codigo*' => ['distinct','string' ,'exists:productos,codigo'],
            'motivo' => ['required', 'string', 'max:500'],
            'cliente_ci' => ['required', 'numeric', 'exists:clientes,ci'],
        ];
    }
}
