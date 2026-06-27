<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FoodLogStoreRequest extends FormRequest
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
            'logged_at' => ['required', 'date'],
            'meal_type' => ['required', Rule::enum(MealType::class)],
            'food_item_id' => ['required', 'integer', 'exists:food_items,id'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
            'unit' => ['required', 'string', 'max:20'],
        ];
    }
}
