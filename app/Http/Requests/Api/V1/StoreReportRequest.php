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
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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
