<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unitId = $this->route('unit')?->id;

        return [
            'directorate_id' => [
                'required',
                Rule::exists('directorates', 'id')->whereNull('deleted_at'),
            ],
            'code' => ['required', 'string', 'max:20', Rule::unique('units', 'code')->ignore($unitId)],
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
            'directorate_id.required' => 'Direktorat wajib dipilih.',
            'code.required' => 'Kode unit wajib diisi.',
            'code.unique' => 'Kode unit sudah digunakan.',
            'name.required' => 'Nama unit wajib diisi.',
        ];
    }
}
