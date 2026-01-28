<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $positionId = $this->route('position')?->id;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('positions', 'code')->ignore($positionId)],
            'name' => ['required', 'string', 'max:150'],
            'level' => ['required', 'integer', 'min:0', 'max:100'],
            'can_approve_documents' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $sortOrder = $this->input('sort_order');

        $this->merge([
            'sort_order' => $sortOrder === '' ? null : $sortOrder,
            'can_approve_documents' => $this->boolean('can_approve_documents'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode jabatan wajib diisi.',
            'code.unique' => 'Kode jabatan sudah digunakan.',
            'name.required' => 'Nama jabatan wajib diisi.',
            'level.required' => 'Level jabatan wajib diisi.',
        ];
    }
}
