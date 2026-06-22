<?php

namespace App\Http\Requests\Api\V1;

use App\Services\Api\Schemas\SaleSchema;
use Illuminate\Foundation\Http\FormRequest;

class SaleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return SaleSchema::storeRules();
    }
}
