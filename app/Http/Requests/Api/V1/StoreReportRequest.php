<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Report::class) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:report_categories,id'],
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address_text' => ['required', 'string', 'min:5', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'is_hazard_warning_acknowledged' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:min_width=40,min_height=40,max_width=8000,max_height=8000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:min_width=40,min_height=40,max_width=8000,max_height=8000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.image' => 'The attached evidence must be an image file.',
            'photo.mimes' => 'Images must be in JPG, PNG, or WebP format.',
            'photo.max' => 'The image size may not exceed 10 megabytes.',
            'photo.dimensions' => 'The photo dimensions must be between 40x40 and 8000x8000 pixels.',
            'photos.*.image' => 'All attached files must be valid images.',
            'photos.*.mimes' => 'All images must be in JPG, PNG, or WebP format.',
            'photos.*.max' => 'Each image size may not exceed 10 megabytes.',
            'photos.*.dimensions' => 'Each photo dimensions must be between 40x40 and 8000x8000 pixels.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('category_id')) {
                    return;
                }

                $category = ReportCategory::find($this->integer('category_id'));
                if ($category && $category->is_hazardous && ! $this->boolean('is_hazard_warning_acknowledged')) {
                    $validator->errors()->add(
                        'is_hazard_warning_acknowledged',
                        'You must acknowledge the safety advisory for hazardous incidents before submitting.'
                    );
                }
            },
        ];
    }
}
