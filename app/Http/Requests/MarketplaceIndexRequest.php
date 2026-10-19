<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarketplaceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seccion' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Support\MarketplaceDepartments::ALL))],
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],

            'categoria' => [
                'nullable',
                'integer',
                'exists:categorias,id_categoria',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => $this->filled('q')
                ? trim((string) $this->input('q'))
                : null,
        ]);
    }
}
