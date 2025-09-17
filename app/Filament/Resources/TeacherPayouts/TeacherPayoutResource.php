<?php

namespace App\Filament\Resources\TeacherPayouts;

use App\Filament\Resources\TeacherPayouts\Pages\CreateTeacherPayout;
use App\Filament\Resources\TeacherPayouts\Pages\EditTeacherPayout;
use App\Filament\Resources\TeacherPayouts\Pages\ListTeacherPayouts;
use App\Filament\Resources\TeacherPayouts\Schemas\TeacherPayoutForm;
use App\Filament\Resources\TeacherPayouts\Tables\TeacherPayoutsTable;
use App\Models\TeacherPayout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeacherPayoutResource extends Resource
{
    protected static ?string $model = TeacherPayout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Teacher payouts';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return TeacherPayoutForm::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->isTeacher()) {
            return $query->where('teacher_id', $user->id);
        }

        return $query;
    }

    public static function getModelLabel(): string
    {
        return __('teacher payout');
    }

    public static function getNavigationLabel(): string
    {
        return __(static::$navigationLabel);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeacherPayouts::route('/'),
            'create' => CreateTeacherPayout::route('/create'),
            'edit' => EditTeacherPayout::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function table(Table $table): Table
    {
        return TeacherPayoutsTable::configure($table);
    }
}
