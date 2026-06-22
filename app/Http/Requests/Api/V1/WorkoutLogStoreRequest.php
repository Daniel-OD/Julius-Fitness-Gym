<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class WorkoutLogStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'plan_day_id' => ['nullable', 'integer', 'exists:workout_plan_days,id'],
            'logged_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'sets' => ['nullable', 'array'],
            'sets.*.exercise_id' => ['required', 'integer', 'exists:exercise_library,id'],
            'sets.*.set_number' => ['required', 'integer', 'min:1'],
            'sets.*.reps' => ['nullable', 'integer', 'min:0'],
            'sets.*.weight' => ['nullable', 'numeric', 'min:0'],
            'sets.*.duration_seconds' => ['nullable', 'integer', 'min:0'],
            'sets.*.rest_seconds' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
