<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe tu nombre completo.',
            'name.max' => 'Tu nombre debe contener como máximo 150 caracteres.',
            'email.required' => 'Escribe tu correo electrónico.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado en otra cuenta.',
            'email.max' => 'El correo debe contener como máximo 255 caracteres.',
        ];
    }
}
