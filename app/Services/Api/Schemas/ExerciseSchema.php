<?php

namespace App\Services\Api\Schemas;

use App\Models\Exercise;

final class ExerciseSchema
{
    private function __construct() {}

    /**
     * @return array{
     *   searchable: list<string>,
     *   sortable: list<string>,
     *   default_sort: string,
     *   status_column: string|null,
     *   includes: list<string>,
     *   filters: array<string, array{type: string, column: string}>
     * }
     */
    public static function queryRules(): array
    {
        return [
            'searchable' => ['name', 'equipment', 'instructions'],
            'sortable' => ['id', 'name', 'category', 'created_at'],
            'default_sort' => 'name',
            'status_column' => 'is_active',
            'includes' => [],
            'filters' => [
                'category' => ['type' => 'exact', 'column' => 'category'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resource(Exercise $exercise): array
    {
        return [
            'id' => (int) $exercise->id,
            'name' => (string) $exercise->name,
            'category' => $exercise->category?->value,
            'muscle_groups' => is_array($exercise->muscle_groups) ? $exercise->muscle_groups : [],
            'equipment' => $exercise->equipment,
            'instructions' => $exercise->instructions,
            'video_url' => $exercise->video_url,
            'image' => $exercise->image,
            'is_active' => (bool) $exercise->is_active,
        ];
    }
}
