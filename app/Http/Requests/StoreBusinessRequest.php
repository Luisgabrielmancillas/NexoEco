<?php

namespace App\Http\Requests;

use App\Support\ManzanilloBoundary;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBusinessRequest extends FormRequest
{
    public const DAYS = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'];

    public function authorize(): bool
    {
        return $this->user()->tieneTipo('vendedor');
    }

    public function rules(): array
    {
        $rules = [
            'nombre_tienda' => ['required', 'string', 'max:120'],
            'descripcion_tienda' => ['required', 'string', 'min:20', 'max:3000'],
            'razon_social' => ['nullable', 'string', 'max:150'],
            'giro' => ['required', 'string', 'max:100'],
            'tipo_negocio' => ['required', Rule::in(['local', 'servicios', 'en_linea', 'mixto'])],
            'telefono' => ['required', 'string', 'regex:/^[+0-9() .-]{7,30}$/'],
            'email_contacto' => ['required', 'email', 'max:255'],
            'sitio_web' => ['nullable', 'url:http,https', 'max:500'],
            'direccion' => ['required', 'string', 'max:250'],
            'colonia' => ['required', 'string', 'max:100'],
            'ciudad' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'regex:/^[0-9]{5}$/'],
            'referencias' => ['nullable', 'string', 'max:500'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'servicios' => ['nullable', 'array'],
            'servicios.*' => [Rule::in(['local', 'recoger', 'domicilio', 'envios'])],
            'logo' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:min_width=64,min_height=64,max_width=6000,max_height=6000'],
            'portada' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144', 'dimensions:min_width=320,min_height=120,max_width=6000,max_height=6000'],
            'horarios' => ['required', 'array:'.implode(',', array_keys(self::DAYS))],
        ];
        foreach (self::DAYS as $day => $label) {
            $rules["horarios.$day"] = ['required', 'array:abierto,inicio,fin'];
            $rules["horarios.$day.abierto"] = ['required', 'boolean'];
            $rules["horarios.$day.inicio"] = ['nullable', "required_if:horarios.$day.abierto,1", 'date_format:H:i'];
            $rules["horarios.$day.fin"] = ['nullable', "required_if:horarios.$day.abierto,1", 'date_format:H:i'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $validator->errors()->has('latitud') && ! $validator->errors()->has('longitud') && ! app(ManzanilloBoundary::class)->contains((float) $this->input('latitud'), (float) $this->input('longitud'))) {
                $validator->errors()->add('latitud', ManzanilloBoundary::MESSAGE);
            }
            $open = 0;
            foreach (self::DAYS as $day => $label) {
                if ($this->boolean("horarios.$day.abierto")) {
                    $open++;
                    if ($this->input("horarios.$day.inicio") === $this->input("horarios.$day.fin")) {
                        $validator->errors()->add("horarios.$day.fin", "$label: la apertura y el cierre deben ser distintos.");
                    }
                }
            }
            if (! $open) {
                $validator->errors()->add('horarios', 'Selecciona al menos un día de atención.');
            }
        });
    }

    public function attributes(): array
    {
        return ['nombre_tienda' => 'nombre de la tienda', 'descripcion_tienda' => 'descripción', 'tipo_negocio' => 'tipo de negocio', 'email_contacto' => 'correo de contacto', 'codigo_postal' => 'código postal', 'latitud' => 'ubicación en el mapa', 'longitud' => 'ubicación en el mapa'];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'Escribe un correo válido en :attribute.',
            'url' => 'Escribe una dirección web que empiece con https:// o http://.',
            'logo.image' => 'El logo debe ser una imagen.',
            'portada.image' => 'La portada debe ser una imagen.',
            'logo.max' => 'El logo debe pesar como máximo 3 MB.',
            'portada.max' => 'La portada debe pesar como máximo 6 MB.',
            'logo.dimensions' => 'El logo debe medir entre 64 y 6,000 píxeles por lado.',
            'portada.dimensions' => 'La portada debe medir al menos 320 × 120 px y como máximo 6,000 px por lado.',
            'horarios.*.inicio.required_if' => 'Indica la hora de apertura de cada día seleccionado.',
            'horarios.*.fin.required_if' => 'Indica la hora de cierre de cada día seleccionado.',
        ];
    }
}
