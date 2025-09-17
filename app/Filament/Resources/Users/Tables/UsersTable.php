<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Role;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('role.name')
                    ->label(__('Role'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Owner' => 'danger',
                        'Admin' => 'warning',
                        'Teacher' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __($state)),
                TextColumn::make('email_verified_at')
                    ->label(__('Email verified at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ->defaultGroup('role.name')
            ->defaultGroup(Group::make('role.name')->label(__('Role'))->getTitleFromRecordUsing(fn (User $record): string => __($record->role->name)))
            // ->paginated(false)
            ->paginationPageOptions([100])
            ->defaultPaginationPageOption(100)
            ->filters([
                SelectFilter::make('role')
                    ->label(__('Role'))
                    ->relationship('role', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $record) => __($record->name))
                    ->indicateUsing(function (array $data): ?string {
                        return (empty($data['value']) ? null : __(Role::findOrFail($data['value'])->name));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
