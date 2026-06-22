<?php

namespace App\Filament\Resources\NutritionPlans;

use App\Filament\Resources\NutritionPlans\Pages\CreateNutritionPlan;
use App\Filament\Resources\NutritionPlans\Pages\EditNutritionPlan;
use App\Filament\Resources\NutritionPlans\Pages\ListNutritionPlans;
use App\Filament\Resources\NutritionPlans\Schemas\NutritionPlanForm;
use App\Models\NutritionPlan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NutritionPlanResource extends Resource
{
    protected static ?string $model = NutritionPlan::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.nutrition_plans.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.nutrition_plans.plural');
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return NutritionPlanForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('member.name')->label(__('app.fields.member')),
            TextColumn::make('name')->searchable(),
            TextColumn::make('daily_calories')->label(__('app.fitness.daily_calories')),
            TextColumn::make('start_date')->date(),
            TextColumn::make('end_date')->date(),
        ]);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListNutritionPlans::route('/'),
            'create' => CreateNutritionPlan::route('/create'),
            'edit' => EditNutritionPlan::route('/{record}/edit'),
        ];
    }
}
