<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Report|null $report */
        $report = $this->route('report');

        return $report !== null && ($this->user()?->can('update', $report) ?? false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'landmark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
