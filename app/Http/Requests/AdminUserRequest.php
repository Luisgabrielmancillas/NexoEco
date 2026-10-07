<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\AdministratorLimit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->activo && $this->user()->tieneTipo('administrador');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email'),
        ]);
    }

    public function rules(): array
    {
        $rules = ['name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->route('user')?->id)]];
        if ($this->isMethod('post')) {
            $rules['rol'] = ['required', Rule::in(['comprador', 'moderador', 'administrador'])];
            $rules['password'] = ['required', 'string', 'min:8', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return ['name.required' => 'Escribe el nombre completo.', 'name.max' => 'El nombre debe contener como máximo 150 caracteres.', 'email.required' => 'Escribe el correo electrónico.', 'email.email' => 'Escribe un correo electrónico válido.', 'email.unique' => 'Este correo ya está registrado.', 'rol.in' => 'Puedes crear compradores, moderadores o administradores. Los vendedores requieren documentos.', 'rol.required' => 'Selecciona el rol.', 'password.required' => 'Asigna una contraseña.', 'password.min' => 'La contraseña debe contener al menos 8 caracteres.'];
    }

    public function after(): array
    {
        return [function (\Illuminate\Validation\Validator $validator) {
            if ($this->isMethod('post') && $this->input('rol') === 'administrador' && app(AdministratorLimit::class)->count() >= AdministratorLimit::MAX_ACCOUNTS) {
                $validator->errors()->add('rol', AdministratorLimit::MESSAGE);
            }
        }];
    }
}
