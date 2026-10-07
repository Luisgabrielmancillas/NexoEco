<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudVendedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rfc' => [
                'required',
                'string',
                'max:13',
                'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/i',
            ],

            'curp' => [
                'required',
                'string',
                'size:18',
                'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/i',
            ],

            'telefono' => [
                'required',
                'string',
                'min:10',
                'max:20',
                'regex:/^[0-9+\s()\-]+$/',
            ],

            'domicilio_fiscal' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],

            'identificacion_frente' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'identificacion_reverso' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'constancia_fiscal' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'comprobante_domicilio' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rfc' => mb_strtoupper(
                trim(
                    (string) $this->input('rfc')
                )
            ),

            'curp' => mb_strtoupper(
                trim(
                    (string) $this->input('curp')
                )
            ),

            'telefono' => trim(
                (string) $this->input('telefono')
            ),

            'domicilio_fiscal' => trim(
                (string) $this->input(
                    'domicilio_fiscal'
                )
            ),
        ]);
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser un texto válido.',
            'max' => 'El campo :attribute debe contener como máximo :max caracteres.',
            'min' => 'El campo :attribute debe contener al menos :min caracteres.',
            'file' => 'Adjunta un archivo válido en :attribute.',
            'telefono.regex' => 'Ingresa un teléfono con formato válido.',
            'rfc.regex' => 'Ingresa un RFC con formato válido.',

            'curp.regex' => 'Ingresa una CURP con formato válido.',

            'curp.size' => 'La CURP debe contener 18 caracteres.',

            '*.mimes' => 'Solo se permiten archivos JPG, PNG o PDF.',

            'identificacion_frente.max' => 'Cada archivo debe pesar como máximo 5 MB.',
            'identificacion_reverso.max' => 'Cada archivo debe pesar como máximo 5 MB.',
            'constancia_fiscal.max' => 'Cada archivo debe pesar como máximo 5 MB.',
            'comprobante_domicilio.max' => 'Cada archivo debe pesar como máximo 5 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_completo' => 'nombre completo',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'rfc' => 'RFC',
            'curp' => 'CURP',
            'telefono' => 'teléfono',
            'domicilio_fiscal' => 'domicilio fiscal',
            'identificacion_frente' => 'identificación oficial (frente)',
            'identificacion_reverso' => 'identificación oficial (reverso)',
            'constancia_fiscal' => 'constancia de situación fiscal',
            'comprobante_domicilio' => 'comprobante de domicilio',
        ];
    }
}
