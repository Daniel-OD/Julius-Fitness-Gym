<?php

namespace App\Http\Resources\V1;

use App\Models\Exercise;
use App\Services\Api\Schemas\ExerciseSchema;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Exercise */
class ExerciseResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[\Override]
    public function toArray(Request $request): array
    {
        /** @var Exercise $exercise */
        $exercise = $this->resource;

        return ExerciseSchema::resource($exercise);
    }
}
