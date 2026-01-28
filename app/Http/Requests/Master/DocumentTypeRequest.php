<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentTypeId = $this->route('document_type')?->id;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('document_types', 'code')->ignore($documentTypeId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'prefix' => ['required', 'string', 'max:10'],
            'requires_approval' => ['boolean'],
            'has_expiry' => ['boolean'],
            'default_retention_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $sortOrder = $this->input('sort_order');
        $retentionDays = $this->input('default_retention_days');

        $this->merge([
            'sort_order' => $sortOrder === '' ? null : $sortOrder,
            'default_retention_days' => $retentionDays === '' ? null : $retentionDays,
            'requires_approval' => $this->boolean('requires_approval'),
            'has_expiry' => $this->boolean('has_expiry'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode jenis dokumen wajib diisi.',
            'code.unique' => 'Kode jenis dokumen sudah digunakan.',
            'name.required' => 'Nama jenis dokumen wajib diisi.',
            'prefix.required' => 'Prefix nomor dokumen wajib diisi.',
        ];
    }
}
