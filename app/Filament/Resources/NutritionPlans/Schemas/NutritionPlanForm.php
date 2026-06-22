<?php

namespace App\Filament\Resources\NutritionPlans\Schemas;

use App\Enums\MealType;
use App\Models\FoodItem;
use App\Models\Member;
use App\Services\Fitness\NutritionMacroCalculator;
use App\Support\Fitness\InstructorAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class NutritionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Select::make('member_id')
                ->label(__('app.fields.member'))
                ->options(function (): array {
                    $user = auth()->user();
                    $query = Member::query()->orderBy('name');
                    $ids = $user ? InstructorAccess::assignedMemberIds($user) : null;
                    if (is_array($ids)) {
                        $query->whereIn('id', $ids);
                    }

                    return $query->pluck('name', 'id')->all();
                })
                ->searchable()
                ->required(),
            TextInput::make('name')->label(__('app.fields.name'))->required(),
            TextInput::make('daily_calories')
                ->label(__('app.fitness.daily_calories'))
                ->numeric()
                ->live()
                ->afterStateUpdated(function (?string $state, Set $set): void {
                    if (! is_numeric($state)) {
                        return;
                    }
                    $targets = app(NutritionMacroCalculator::class)->targetsFromCalories((int) $state);
                    $set('protein_g', $targets['protein_g']);
                    $set('carbs_g', $targets['carbs_g']);
                    $set('fat_g', $targets['fat_g']);
                }),
            TextInput::make('protein_g')->label(__('app.fitness.protein_g'))->numeric(),
            TextInput::make('carbs_g')->label(__('app.fitness.carbs_g'))->numeric(),
            TextInput::make('fat_g')->label(__('app.fitness.fat_g'))->numeric(),
            DatePicker::make('start_date')->required(),
            DatePicker::make('end_date'),
            Textarea::make('notes')->rows(2),
            Repeater::make('meals')
                ->relationship()
                ->label(__('app.fitness.meals'))
                ->schema([
                    Select::make('meal_type')
                        ->options(collect(MealType::cases())->mapWithKeys(
                            fn (MealType $m): array => [$m->value => $m->getLabel()],
                        )->all())
                        ->required(),
                    TextInput::make('name'),
                    TextInput::make('order')->numeric()->default(0),
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('food_item_id')
                                ->label(__('app.fitness.food_item'))
                                ->options(fn (): array => FoodItem::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->required(),
                            TextInput::make('quantity')->numeric()->default(100)->required(),
                            Select::make('unit')
                                ->options(['g' => 'g', 'serving' => __('app.fitness.serving')])
                                ->default('g'),
                        ])
                        ->columns(3)
                        ->addActionLabel(__('app.fitness.add_food')),
                ])
                ->addActionLabel(__('app.fitness.add_meal')),
        ]);
    }
}
