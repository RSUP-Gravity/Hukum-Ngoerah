<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('document_category')?->id;

        return [
            'document_type_id' => [
                'required',
                Rule::exists('document_types', 'id')->whereNull('deleted_at'),
            ],
            'code' => ['required', 'string', 'max:20', Rule::unique('document_categories', 'code')->ignore($categoryId)],
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
            'document_type_id.required' => 'Jenis dokumen wajib dipilih.',
            'code.required' => 'Kode kategori wajib diisi.',
            'code.unique' => 'Kode kategori sudah digunakan.',
            'name.required' => 'Nama kategori wajib diisi.',
        ];
    }
}
