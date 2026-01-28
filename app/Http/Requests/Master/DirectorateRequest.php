<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DirectorateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $directorateId = $this->route('directorate')?->id;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('directorates', 'code')->ignore($directorateId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $sortOrder = $this->input('sort_order');

        $this->merge([
            'sort_order' => $sortOrder === '' ? null : $sortOrder,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode direktorat wajib diisi.',
            'code.unique' => 'Kode direktorat sudah digunakan.',
            'name.required' => 'Nama direktorat wajib diisi.',
        ];
    }
}
